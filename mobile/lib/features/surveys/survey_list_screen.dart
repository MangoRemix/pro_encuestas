import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/db/app_database.dart';
import '../auth/auth_controller.dart';
import 'survey_repository.dart';

class SurveyListScreen extends ConsumerStatefulWidget {
  const SurveyListScreen({super.key});

  @override
  ConsumerState<SurveyListScreen> createState() => _SurveyListScreenState();
}

class _SurveyListScreenState extends ConsumerState<SurveyListScreen> {
  List<CachedSurvey> _surveys = [];
  bool _loading = true;
  int? _downloadingSurveyId;
  String? _bannerMessage;

  @override
  void initState() {
    super.initState();
    _refresh();
  }

  Future<void> _refresh() async {
    setState(() => _loading = true);

    final repository = ref.read(surveyRepositoryProvider);

    try {
      await repository.downloadCatalogs();
      final remote = await repository.fetchAssignedSurveys();

      for (final summary in remote) {
        if (!await repository.isSurveyDownloaded(summary.id)) {
          await repository.downloadSurvey(summary.id);
        }
      }

      _bannerMessage = null;
    } catch (_) {
      _bannerMessage =
          'Sin conexión: mostrando encuestas ya descargadas en este dispositivo.';
    }

    final cached = await repository.cachedSurveys();

    if (mounted) {
      setState(() {
        _surveys = cached;
        _loading = false;
      });
    }
  }

  Future<void> _openSurvey(CachedSurvey survey) async {
    setState(() => _downloadingSurveyId = survey.id);

    try {
      final repository = ref.read(surveyRepositoryProvider);

      if (!await repository.isSurveyDownloaded(survey.id)) {
        await repository.downloadSurvey(survey.id);
      }

      if (mounted) {
        context.push('/respondent/${survey.id}');
      }
    } catch (error) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'No se pudo descargar la encuesta. Necesitas conexión la primera vez.',
            ),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _downloadingSurveyId = null);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Encuestas disponibles'),
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            tooltip: 'Historial',
            onPressed: () => context.push('/history'),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Cerrar sesión',
            onPressed: () => ref.read(authControllerProvider.notifier).logout(),
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                children: [
                  if (_bannerMessage != null)
                    Container(
                      width: double.infinity,
                      color: Colors.amber.shade100,
                      padding: const EdgeInsets.all(12),
                      child: Text(_bannerMessage!),
                    ),
                  if (_surveys.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(32),
                      child: Text(
                        'No tienes encuestas asignadas por ahora. '
                        'Cuando un administrador te asigne una, aparecerá aquí.',
                        textAlign: TextAlign.center,
                      ),
                    ),
                  for (final survey in _surveys)
                    ListTile(
                      leading: _downloadingSurveyId == survey.id
                          ? const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.assignment_outlined),
                      title: Text(survey.name),
                      subtitle: Text(
                        'Del ${_formatDate(survey.initDate)} al ${_formatDate(survey.finishDate)}',
                      ),
                      onTap: _downloadingSurveyId == null
                          ? () => _openSurvey(survey)
                          : null,
                    ),
                ],
              ),
      ),
    );
  }

  String _formatDate(DateTime date) =>
      '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
}
