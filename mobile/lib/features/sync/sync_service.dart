import 'package:dio/dio.dart';
import 'package:drift/drift.dart' show Value;
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/db/app_database.dart';
import '../../core/network/api_client.dart';
import '../../core/providers.dart';

class SyncResult {
  SyncResult({required this.uploaded, required this.failed});

  final int uploaded;
  final int failed;
}

/// Sube las instancias "preparadas" (o previamente fallidas) al backend, una
/// por una, vía el endpoint atómico `result/batch-instance`. Nunca se marca
/// una instancia como completada sin una respuesta 2xx explícita del
/// servidor — si una falla, se registra el error y se continúa con las
/// demás (no se pierde nada ni se detiene todo el lote).
class SyncService {
  SyncService({required ApiClient apiClient, required AppDatabase db})
      : _apiClient = apiClient,
        _db = db;

  final ApiClient _apiClient;
  final AppDatabase _db;

  Future<SyncResult> uploadPending() async {
    final all = await _db.watchAllInstances().first;
    final pending =
        all.where((i) => i.status == InstanceStatus.preparada).toList();

    var uploaded = 0;
    var failed = 0;

    for (final instance in pending) {
      final success = await _uploadOne(instance);

      if (success) {
        uploaded++;
      } else {
        failed++;
      }
    }

    return SyncResult(uploaded: uploaded, failed: failed);
  }

  Future<bool> uploadSingle(String instanceUuid) async {
    final instance = await _db.instanceByUuid(instanceUuid);

    if (instance == null) return false;

    return _uploadOne(instance);
  }

  Future<bool> _uploadOne(SurveyInstance instance) async {
    try {
      final answers = await _db.answersForInstance(instance.localUuid);

      final response = await _apiClient.dio.post('result/batch-instance', data: {
        'instance_uuid': instance.localUuid,
        'survey_id': instance.surveyId,
        'activity_id': instance.activityId,
        'pollster_id': instance.pollsterPersonId,
        'respondent': {
          'sex_id': instance.respondentSexId,
          'age': instance.respondentAge,
          'parish_id': instance.respondentParishId,
        },
        'answers': [
          for (final a in answers)
            {'question_id': a.questionId, 'answer_id': a.answerId},
        ],
      });

      final serverPersonId = response.data['server_person_id'] as int?;

      await _db.upsertInstance(SurveyInstancesCompanion(
        localUuid: Value(instance.localUuid),
        surveyId: Value(instance.surveyId),
        pollsterPersonId: Value(instance.pollsterPersonId),
        status: const Value(InstanceStatus.completada),
        uploadedAt: Value(DateTime.now()),
        serverPersonId: Value(serverPersonId),
        updatedAt: Value(DateTime.now()),
      ));

      return true;
    } on DioException catch (error) {
      await _db.upsertInstance(SurveyInstancesCompanion(
        localUuid: Value(instance.localUuid),
        surveyId: Value(instance.surveyId),
        pollsterPersonId: Value(instance.pollsterPersonId),
        uploadAttempts: Value(instance.uploadAttempts + 1),
        lastUploadError: Value(_describeError(error)),
        updatedAt: Value(DateTime.now()),
      ));

      return false;
    }
  }

  String _describeError(DioException error) {
    final data = error.response?.data;

    if (data is Map && data['message'] is String) {
      return data['message'] as String;
    }

    return error.message ?? 'Error de red desconocido';
  }
}

final syncServiceProvider = Provider<SyncService>((ref) {
  return SyncService(
    apiClient: ref.watch(apiClientProvider),
    db: ref.watch(appDatabaseProvider),
  );
});
