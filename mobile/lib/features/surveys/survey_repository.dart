import 'package:drift/drift.dart' show Value;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/db/app_database.dart';
import '../../core/network/api_client.dart';
import '../../core/providers.dart';

class RemoteParish {
  const RemoteParish({required this.id, required this.name});

  final int id;
  final String name;

  factory RemoteParish.fromJson(Map<String, dynamic> json) => RemoteParish(
        id: json['id'] as int,
        name: json['name'] as String,
      );
}

/// Una actividad asignada al encuestador: una encuesta aplicada en una o
/// varias parroquias, dentro de un rango de fechas concreto.
class RemoteActivitySummary {
  RemoteActivitySummary({
    required this.id,
    required this.surveyId,
    required this.surveyName,
    required this.parishes,
    required this.initDate,
    required this.finishDate,
  });

  final int id;
  final int surveyId;
  final String surveyName;
  final List<RemoteParish> parishes;
  final DateTime initDate;
  final DateTime finishDate;

  factory RemoteActivitySummary.fromJson(Map<String, dynamic> json) {
    final survey = json['survey'] as Map<String, dynamic>?;
    final parishes = (json['parishes'] as List<dynamic>? ?? [])
        .map((p) => RemoteParish.fromJson(p as Map<String, dynamic>))
        .toList();

    return RemoteActivitySummary(
      id: json['id'] as int,
      surveyId: json['survey_id'] as int,
      surveyName: (survey?['name'] as String?) ?? '',
      parishes: parishes,
      initDate: DateTime.parse(json['init_date'] as String),
      finishDate: DateTime.parse(json['finish_date'] as String),
    );
  }
}

class SurveyRepository {
  SurveyRepository({required ApiClient apiClient, required AppDatabase db})
      : _apiClient = apiClient,
        _db = db;

  final ApiClient _apiClient;
  final AppDatabase _db;

  /// Actividades asignadas y vigentes para el encuestador autenticado. Si no
  /// hay conexión, se propaga el error para que la pantalla pueda mostrar el
  /// listado ya cacheado en su lugar.
  Future<List<RemoteActivitySummary>> fetchAssignedActivities() async {
    final response = await _apiClient.dio.get('mobile/activities');
    final data = response.data as List<dynamic>;

    return data
        .map((item) => RemoteActivitySummary.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<List<CachedActivity>> cachedActivities() => _db.allCachedActivities();

  Future<void> cacheActivities(List<RemoteActivitySummary> activities) async {
    await _db.replaceCatalogActivities([
      for (final activity in activities)
        CachedActivitiesCompanion.insert(
          id: Value(activity.id),
          surveyId: activity.surveyId,
          surveyName: activity.surveyName,
          parishIdsCsv: activity.parishes.map((p) => p.id).join(','),
          parishNamesCsv: activity.parishes.map((p) => p.name).join(','),
          initDate: activity.initDate,
          finishDate: activity.finishDate,
        ),
    ]);
  }

  Future<bool> isSurveyDownloaded(int surveyId) async {
    final categories = await _db.categoriesForSurvey(surveyId);

    return categories.isNotEmpty;
  }

  /// Descarga la estructura completa de la encuesta (categorías->preguntas->
  /// respuestas) UNA vez y la cachea localmente — de ahí en adelante se
  /// puede llenar sin conexión.
  Future<void> downloadSurvey(int surveyId) async {
    final response = await _apiClient.dio.get('survey/show-full/$surveyId');
    final survey = response.data as Map<String, dynamic>;

    await _db.replaceCatalogSurveys([
      CachedSurveysCompanion.insert(
        id: Value(survey['id'] as int),
        name: survey['name'] as String,
      ),
    ]);

    final categories = <CachedCategoriesCompanion>[];
    final questions = <CachedQuestionsCompanion>[];
    final answers = <CachedAnswersCompanion>[];

    for (final category in (survey['categories'] as List<dynamic>)) {
      final categoryMap = category as Map<String, dynamic>;
      categories.add(CachedCategoriesCompanion.insert(
        id: Value(categoryMap['id'] as int),
        surveyId: surveyId,
        name: categoryMap['name'] as String,
        order: categoryMap['order'] as int,
      ));

      for (final question in (categoryMap['questions'] as List<dynamic>)) {
        final questionMap = question as Map<String, dynamic>;
        questions.add(CachedQuestionsCompanion.insert(
          id: Value(questionMap['id'] as int),
          categoryId: categoryMap['id'] as int,
          name: questionMap['name'] as String,
          order: questionMap['order'] as int,
          allowsMultipleAnswers: Value(
            (questionMap['allows_multiple_answers'] as bool?) ?? false,
          ),
        ));

        for (final answer in (questionMap['answers'] as List<dynamic>)) {
          final answerMap = answer as Map<String, dynamic>;
          answers.add(CachedAnswersCompanion.insert(
            id: Value(answerMap['id'] as int),
            questionId: questionMap['id'] as int,
            name: answerMap['name'] as String,
            order: answerMap['order'] as int,
          ));
        }
      }
    }

    await _db.replaceSurveyStructure(
      surveyId: surveyId,
      categories: categories,
      questions: questions,
      answers: answers,
    );
  }

  Future<void> downloadCatalogs() async {
    final sexesResponse = await _apiClient.dio.get('sex/show-all');
    final sexes = (sexesResponse.data as List<dynamic>)
        .map((s) => CachedSexesCompanion.insert(
              id: Value((s as Map<String, dynamic>)['id'] as int),
              abbreviation: s['abbreviation'] as String,
              description: s['description'] as String,
            ))
        .toList();
    await _db.replaceSexes(sexes);
  }
}

final surveyRepositoryProvider = Provider<SurveyRepository>((ref) {
  return SurveyRepository(
    apiClient: ref.watch(apiClientProvider),
    db: ref.watch(appDatabaseProvider),
  );
});
