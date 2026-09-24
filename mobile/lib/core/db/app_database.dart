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
/// La encuesta en sí ya no tiene parroquia ni fechas propias (eso vive en
/// `CachedActivities` — ver más abajo); esta tabla solo cachea su nombre
/// para no depender de tener conexión al abrir la app.
///
/// Nota: cada tabla declara su nombre de clase de fila explícitamente con
/// @DataClassName — la pluralización automática de Drift ("strip trailing
/// s") acierta con Survey/Question/Answer/Instance pero falla con
/// Categories/Sexes/Activities, así que se evita depender de esa heurística
/// en absoluto.
@DataClassName('CachedSurvey')
class CachedSurveys extends Table {
  IntColumn get id => integer()(); // id del servidor
  TextColumn get name => text()();
  DateTimeColumn get downloadedAt => dateTime().withDefault(currentDateAndTime)();

  @override
  Set<Column> get primaryKey => {id};
}

/// Actividades asignadas al encuestador autenticado: una encuesta aplicada
/// en una o varias parroquias durante un rango de fechas. Reemplaza lo que
/// antes vivía directamente en CachedSurveys (parish_id/init_date/finish_date)
/// — ahora la encuesta puede tener varias de estas actividades.
///
/// Las parroquias se guardan como listas separadas por coma (misma cantidad
/// y orden en ambas columnas) en vez de una tabla aparte — esta tabla es
/// solo caché redescargable, no hace falta más estructura.
@DataClassName('CachedActivity')
class CachedActivities extends Table {
  IntColumn get id => integer()(); // id de la actividad en el servidor
  IntColumn get surveyId => integer()();
  TextColumn get surveyName => text()();
  TextColumn get parishIdsCsv => text()();
  TextColumn get parishNamesCsv => text()();
  DateTimeColumn get initDate => dateTime()();
  DateTimeColumn get finishDate => dateTime()();
  DateTimeColumn get downloadedAt => dateTime().withDefault(currentDateAndTime)();

  @override
  Set<Column> get primaryKey => {id};
}

extension CachedActivityParishes on CachedActivity {
  List<int> get parishIds => parishIdsCsv
      .split(',')
      .where((s) => s.isNotEmpty)
      .map(int.parse)
      .toList();

  List<String> get parishNames =>
      parishNamesCsv.split(',').where((s) => s.isNotEmpty).toList();

  String get parishLabel => parishNames.join(', ');
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
  // Actividad de campo (encuesta+parroquia+fechas) a la que pertenece esta
  // instancia. Nullable solo por compatibilidad con filas creadas antes de
  // esta columna existir; toda instancia nueva siempre la trae.
  IntColumn get activityId => integer().nullable()();
  IntColumn get pollsterPersonId => integer()();

  IntColumn get respondentSexId => integer().nullable()();
  IntColumn get respondentAge => integer().nullable()();
  // La parroquia del encuestado ya no la elige el encuestador: se
  // autocompleta con la de la actividad asignada.
  IntColumn get respondentParishId => integer().nullable()();

  TextColumn get status =>
      text().withDefault(const Constant(InstanceStatus.incompleta))();

  // Categoría en la que se quedó el encuestador — ya no se usa para
  // reanudar (ver currentQuestionId), se deja sin tocar para no complicar
  // la migración.
  IntColumn get currentCategoryId => integer().nullable()();

  // Pregunta exacta en la que se quedó el encuestador — el llenado avanza
  // de a una pregunta, así que esto es lo que permite reanudar exactamente
  // donde lo dejó si cierra la app a medias.
  IntColumn get currentQuestionId => integer().nullable()();

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
    CachedActivities,
    CachedCategories,
    CachedQuestions,
    CachedAnswers,
    CachedSexes,
    SurveyInstances,
    InstanceAnswers,
  ],
)
class AppDatabase extends _$AppDatabase {
  AppDatabase() : super(_openConnection());

  @override
  int get schemaVersion => 4;

  @override
  MigrationStrategy get migration => MigrationStrategy(
        onCreate: (m) => m.createAll(),
        onUpgrade: (m, from, to) async {
          if (from < 2) {
            // CachedSurveys/CachedParishes son solo caché redescargable —
            // más simple recrearlas que migrar columna por columna. Los
            // datos reales del encuestador (SurveyInstances/InstanceAnswers)
            // se preservan.
            await m.deleteTable('cached_surveys');
            await m.createTable(cachedSurveys);
            await m.createTable(cachedActivities);
            // Tabla de la v1 que ya no se usa (el picker de parroquia se
            // quitó del flujo del encuestador).
            await m.deleteTable('cached_parishes');

            await m.addColumn(surveyInstances, surveyInstances.activityId);
          }

          if (from < 3) {
            // El llenado ahora avanza de a una pregunta (no por categoría) —
            // hace falta guardar en cuál se quedó para poder reanudar.
            await m.addColumn(surveyInstances, surveyInstances.currentQuestionId);
          }

          if (from < 4) {
            // Una actividad ahora puede cubrir varias parroquias a la vez
            // (parishId/parishName singulares -> parishIdsCsv/parishNamesCsv).
            // Es solo caché redescargable — se recrea en vez de migrar.
            await m.deleteTable('cached_activities');
            await m.createTable(cachedActivities);
          }
        },
      );

  // ── Catálogos: reemplazo completo en cada descarga ──────────────────────

  Future<void> replaceCatalogSurveys(List<CachedSurveysCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedSurveys, rows));
  }

  Future<void> replaceCatalogActivities(List<CachedActivitiesCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedActivities, rows));
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

  Future<void> replaceSexes(List<CachedSexesCompanion> rows) async {
    await batch((b) => b.insertAllOnConflictUpdate(cachedSexes, rows));
  }

  Future<List<CachedSurvey>> allCachedSurveys() => select(cachedSurveys).get();

  Future<CachedSurvey?> surveyById(int id) =>
      (select(cachedSurveys)..where((t) => t.id.equals(id))).getSingleOrNull();

  Future<List<CachedActivity>> allCachedActivities() =>
      (select(cachedActivities)
            ..orderBy([(t) => OrderingTerm.desc(t.initDate)]))
          .get();

  Future<CachedActivity?> activityById(int id) =>
      (select(cachedActivities)..where((t) => t.id.equals(id)))
          .getSingleOrNull();

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
