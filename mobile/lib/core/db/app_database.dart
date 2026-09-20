import 'dart:io';

import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

part 'app_database.g.dart';

/// Estados de una instancia de encuesta local (una encuesta llenada a una
/// persona en el teléfono, aún no necesariamente subida al servidor).
class InstanceStatus {
  static const incompleta = 'incompleta';
  static const preparada = 'preparada';
  static const completada = 'completada';
}

/// Encuestas descargadas (una vez) para poder llenarlas sin conexión.
///
/// Nota: cada tabla declara su nombre de clase de fila explícitamente con
/// @DataClassName — la pluralización automática de Drift ("strip trailing
/// s") acierta con Survey/Question/Answer/Instance pero falla con
/// Categories/Parishes/Sexes, así que se evita depender de esa heurística
/// en absoluto.
@DataClassName('CachedSurvey')
class CachedSurveys extends Table {
  IntColumn get id => integer()(); // id del servidor
  TextColumn get name => text()();
  DateTimeColumn get initDate => dateTime()();
  DateTimeColumn get finishDate => dateTime()();
  DateTimeColumn get downloadedAt => dateTime().withDefault(currentDateAndTime)();

  @override
  Set<Column> get primaryKey => {id};
}

@DataClassName('CachedCategory')
class CachedCategories extends Table {
  IntColumn get id => integer()();
  IntColumn get surveyId => integer()();
  TextColumn get name => text()();
  IntColumn get order => integer()();

  @override
  Set<Column> get primaryKey => {id};
}

@DataClassName('CachedQuestion')
class CachedQuestions extends Table {
  IntColumn get id => integer()();
  IntColumn get categoryId => integer()();
  TextColumn get name => text()();
  IntColumn get order => integer()();
  BoolColumn get allowsMultipleAnswers =>
      boolean().withDefault(const Constant(false))();

  @override
  Set<Column> get primaryKey => {id};
}

@DataClassName('CachedAnswer')
class CachedAnswers extends Table {
  IntColumn get id => integer()();
  IntColumn get questionId => integer()();
  TextColumn get name => text()();
  IntColumn get order => integer()();

  @override
  Set<Column> get primaryKey => {id};
}

/// Catálogos públicos (parroquias/sexos) para el registro del encuestado.
@DataClassName('CachedParish')
class CachedParishes extends Table {
  IntColumn get id => integer()();
  TextColumn get name => text()();

  @override
  Set<Column> get primaryKey => {id};
}

@DataClassName('CachedSex')
class CachedSexes extends Table {
  IntColumn get id => integer()();
  TextColumn get abbreviation => text()();
  TextColumn get description => text()();

  @override
  Set<Column> get primaryKey => {id};
}

/// Una instancia = una encuesta completa siendo (o ya) llenada para UNA
/// persona. El uuid local es lo que se manda al servidor como clave de
/// idempotencia al subir.
@DataClassName('SurveyInstance')
class SurveyInstances extends Table {
  TextColumn get localUuid => text()();
  IntColumn get surveyId => integer()();
  IntColumn get pollsterPersonId => integer()();

  IntColumn get respondentSexId => integer().nullable()();
  IntColumn get respondentAge => integer().nullable()();
  IntColumn get respondentParishId => integer().nullable()();

  TextColumn get status =>
      text().withDefault(const Constant(InstanceStatus.incompleta))();

  // Categoría en la que se quedó el encuestador — permite reanudar
  // exactamente donde lo dejó si cierra la app a medias.
  IntColumn get currentCategoryId => integer().nullable()();

  DateTimeColumn get createdAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get updatedAt => dateTime().withDefault(currentDateAndTime)();
  DateTimeColumn get uploadedAt => dateTime().nullable()();

  IntColumn get serverPersonId => integer().nullable()();
  IntColumn get uploadAttempts => integer().withDefault(const Constant(0))();
  TextColumn get lastUploadError => text().nullable()();

  @override
  Set<Column> get primaryKey => {localUuid};
}

/// Una fila por cada respuesta marcada. Selección única = una fila por
/// pregunta; selección múltiple = varias filas con distinto answerId.
@DataClassName('InstanceAnswer')
class InstanceAnswers extends Table {
  TextColumn get instanceUuid => text()();
  IntColumn get questionId => integer()();
  IntColumn get answerId => integer()();
  DateTimeColumn get answeredAt => dateTime().withDefault(currentDateAndTime)();

  @override
  Set<Column> get primaryKey => {instanceUuid, questionId, answerId};
}

@DriftDatabase(
  tables: [
    CachedSurveys,
    CachedCategories,
    CachedQuestions,
    CachedAnswers,
    CachedParishes,
    CachedSexes,
    SurveyInstances,
    InstanceAnswers,
  ],
)
class AppDatabase extends _$AppDatabase {
  AppDatabase() : super(_openConnection());

  @override
  int get schemaVersion => 1;

  // ── Catálogos: reemplazo completo en cada descarga ──────────────────────

  Future<void> replaceCatalogSurveys(List<CachedSurveysCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedSurveys, rows));
  }

  Future<void> replaceSurveyStructure({
    required int surveyId,
    required List<CachedCategoriesCompanion> categories,
    required List<CachedQuestionsCompanion> questions,
    required List<CachedAnswersCompanion> answers,
  }) async {
    await transaction(() async {
      await batch((b) => b.insertAllOnConflictUpdate(cachedCategories, categories));
      await batch((b) => b.insertAllOnConflictUpdate(cachedQuestions, questions));
      await batch((b) => b.insertAllOnConflictUpdate(cachedAnswers, answers));
    });
  }

  Future<void> replaceParishes(List<CachedParishesCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedParishes, rows));
  }

  Future<void> replaceSexes(List<CachedSexesCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedSexes, rows));
  }

  Future<List<CachedSurvey>> allCachedSurveys() => select(cachedSurveys).get();

  Future<List<CachedCategory>> categoriesForSurvey(int surveyId) =>
      (select(cachedCategories)
            ..where((t) => t.surveyId.equals(surveyId))
            ..orderBy([(t) => OrderingTerm.asc(t.order)]))
          .get();

  Future<List<CachedQuestion>> questionsForCategory(int categoryId) =>
      (select(cachedQuestions)
            ..where((t) => t.categoryId.equals(categoryId))
            ..orderBy([(t) => OrderingTerm.asc(t.order)]))
          .get();

  Future<List<CachedAnswer>> answersForQuestion(int questionId) =>
      (select(cachedAnswers)
            ..where((t) => t.questionId.equals(questionId))
            ..orderBy([(t) => OrderingTerm.asc(t.order)]))
          .get();

  Future<List<CachedParish>> allParishes() => select(cachedParishes).get();

  Future<List<CachedSex>> allSexes() => select(cachedSexes).get();

  // ── Instancias (encuestas en progreso / listas / subidas) ───────────────

  Stream<List<SurveyInstance>> watchInstancesForSurvey(int surveyId) =>
      (select(surveyInstances)
            ..where((t) => t.surveyId.equals(surveyId))
            ..orderBy([(t) => OrderingTerm.desc(t.updatedAt)]))
          .watch();

  Stream<List<SurveyInstance>> watchAllInstances() =>
      (select(surveyInstances)
            ..orderBy([(t) => OrderingTerm.desc(t.updatedAt)]))
          .watch();

  Future<SurveyInstance?> instanceByUuid(String uuid) =>
      (select(surveyInstances)..where((t) => t.localUuid.equals(uuid)))
          .getSingleOrNull();

  Future<void> upsertInstance(SurveyInstancesCompanion row) =>
      into(surveyInstances).insertOnConflictUpdate(row);

  Future<void> deleteInstance(String uuid) async {
    await transaction(() async {
      await (delete(instanceAnswers)..where((t) => t.instanceUuid.equals(uuid))).go();
      await (delete(surveyInstances)..where((t) => t.localUuid.equals(uuid))).go();
    });
  }

  Future<List<InstanceAnswer>> answersForInstance(String uuid) =>
      (select(instanceAnswers)..where((t) => t.instanceUuid.equals(uuid))).get();

  /// Marca/desmarca una respuesta. Para preguntas de una sola respuesta el
  /// llamador debe borrar primero cualquier otra respuesta de esa pregunta
  /// (ver [replaceSingleAnswer]); para selección múltiple cada llamada
  /// simplemente agrega o quita una fila.
  Future<void> setInstanceAnswer({
    required String instanceUuid,
    required int questionId,
    required int answerId,
  }) =>
      into(instanceAnswers).insertOnConflictUpdate(
        InstanceAnswersCompanion.insert(
          instanceUuid: instanceUuid,
          questionId: questionId,
          answerId: answerId,
        ),
      );

  Future<void> unsetInstanceAnswer({
    required String instanceUuid,
    required int questionId,
    required int answerId,
  }) =>
      (delete(instanceAnswers)
            ..where((t) =>
                t.instanceUuid.equals(instanceUuid) &
                t.questionId.equals(questionId) &
                t.answerId.equals(answerId)))
          .go();

  /// Selección única: reemplaza cualquier respuesta previa de la pregunta
  /// por la nueva (nunca deben coexistir dos respuestas para la misma
  /// pregunta de selección única).
  Future<void> replaceSingleAnswer({
    required String instanceUuid,
    required int questionId,
    required int answerId,
  }) async {
    await transaction(() async {
      await (delete(instanceAnswers)
            ..where((t) =>
                t.instanceUuid.equals(instanceUuid) &
                t.questionId.equals(questionId)))
          .go();
      await setInstanceAnswer(
        instanceUuid: instanceUuid,
        questionId: questionId,
        answerId: answerId,
      );
    });
  }
}

LazyDatabase _openConnection() {
  return LazyDatabase(() async {
    final dbFolder = await getApplicationDocumentsDirectory();
    final file = File(p.join(dbFolder.path, 'pro_encuestas.sqlite'));

    return NativeDatabase.createInBackground(file);
  });
}
