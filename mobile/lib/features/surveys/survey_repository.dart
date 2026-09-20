import 'package:drift/drift.dart' show Value;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/db/app_database.dart';
import '../../core/network/api_client.dart';
import '../../core/providers.dart';

class RemoteSurveySummary {
  RemoteSurveySummary({required this.id, required this.name});

  final int id;
  final String name;

  factory RemoteSurveySummary.fromJson(Map<String, dynamic> json) {
    return RemoteSurveySummary(id: json['id'] as int, name: json['name'] as String);
  }
}

class SurveyRepository {
  SurveyRepository({required ApiClient apiClient, required AppDatabase db})
      : _apiClient = apiClient,
        _db = db;

  final ApiClient _apiClient;
  final AppDatabase _db;

  /// Encuestas asignadas y vigentes para el encuestador autenticado. Si no
  /// hay conexión, se propaga el error para que la pantalla pueda mostrar el
  /// listado ya cacheado en su lugar.
  Future<List<RemoteSurveySummary>> fetchAssignedSurveys() async {
    final response = await _apiClient.dio.get('mobile/surveys');
    final data = response.data as List<dynamic>;

    return data
        .map((item) => RemoteSurveySummary.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  Future<List<CachedSurvey>> cachedSurveys() => _db.allCachedSurveys();

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
        initDate: DateTime.parse(survey['init_date'] as String),
        finishDate: DateTime.parse(survey['finish_date'] as String),
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
    final parishesResponse = await _apiClient.dio.get('parish/show-all');
    final parishes = (parishesResponse.data as List<dynamic>)
        .map((p) => CachedParishesCompanion.insert(
              id: Value((p as Map<String, dynamic>)['id'] as int),
              name: p['name'] as String,
            ))
        .toList();
    await _db.replaceParishes(parishes);

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
