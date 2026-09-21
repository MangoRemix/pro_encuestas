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
  List<CachedActivity> _activities = [];
  bool _loading = true;
  int? _downloadingActivityId;
  String? _bannerMessage;

  // Filtros: un encuestador puede tener varias actividades asignadas en la
  // semana, en distintas parroquias o fechas — esto ayuda a ubicarse.
  String? _parishFilter;
  DateTime? _dateFilter;

  List<String> get _availableParishes =>
      _activities.map((a) => a.parishName).toSet().toList()..sort();

  List<CachedActivity> get _filteredActivities => _activities.where((a) {
        if (_parishFilter != null && a.parishName != _parishFilter) {
          return false;
        }

        if (_dateFilter != null && !_activityCoversDate(a, _dateFilter!)) {
          return false;
        }

        return true;
      }).toList();

  bool _activityCoversDate(CachedActivity activity, DateTime date) {
    final day = DateTime(date.year, date.month, date.day);
    final init = DateTime(
      activity.initDate.year,
      activity.initDate.month,
      activity.initDate.day,
    );
    final finish = DateTime(
      activity.finishDate.year,
      activity.finishDate.month,
      activity.finishDate.day,
    );

    return !day.isBefore(init) && !day.isAfter(finish);
  }

  Future<void> _pickDateFilter() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _dateFilter ?? DateTime.now(),
      firstDate: DateTime(DateTime.now().year - 2),
      lastDate: DateTime(DateTime.now().year + 2),
    );

    if (picked != null) {
      setState(() => _dateFilter = picked);
    }
  }

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
      final remote = await repository.fetchAssignedActivities();
      await repository.cacheActivities(remote);

      for (final activity in remote) {
        if (!await repository.isSurveyDownloaded(activity.surveyId)) {
          await repository.downloadSurvey(activity.surveyId);
        }
      }

      _bannerMessage = null;
    } catch (_) {
      _bannerMessage =
          'Sin conexión: mostrando actividades ya descargadas en este dispositivo.';
    }

    final cached = await repository.cachedActivities();

    if (mounted) {
      setState(() {
        _activities = cached;
        _loading = false;
      });
    }
  }

  Future<void> _openActivity(CachedActivity activity) async {
    setState(() => _downloadingActivityId = activity.id);

    try {
      final repository = ref.read(surveyRepositoryProvider);

      if (!await repository.isSurveyDownloaded(activity.surveyId)) {
        await repository.downloadSurvey(activity.surveyId);
      }

      if (mounted) {
        context.push('/respondent/${activity.id}');
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
        setState(() => _downloadingActivityId = null);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Actividades asignadas'),
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
                  if (_activities.length > 1)
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
                      child: Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        crossAxisAlignment: WrapCrossAlignment.center,
                        children: [
                          SizedBox(
                            width: 200,
                            child: DropdownButtonFormField<String?>(
                              initialValue: _parishFilter,
                              decoration: const InputDecoration(
                                labelText: 'Parroquia',
                                isDense: true,
                              ),
                              items: [
                                const DropdownMenuItem(
                                  value: null,
                                  child: Text('Todas'),
                                ),
                                for (final parish in _availableParishes)
                                  DropdownMenuItem(
                                    value: parish,
                                    child: Text(parish),
                                  ),
                              ],
                              onChanged: (value) =>
                                  setState(() => _parishFilter = value),
                            ),
                          ),
                          ActionChip(
                            avatar: const Icon(Icons.event, size: 18),
                            label: Text(
                              _dateFilter == null
                                  ? 'Fecha'
                                  : _formatDate(_dateFilter!),
                            ),
                            onPressed: _pickDateFilter,
                          ),
                          if (_dateFilter != null)
                            IconButton(
                              icon: const Icon(Icons.clear, size: 18),
                              tooltip: 'Quitar filtro de fecha',
                              onPressed: () =>
                                  setState(() => _dateFilter = null),
                            ),
                        ],
                      ),
                    ),
                  if (_activities.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(32),
                      child: Text(
                        'No tienes actividades asignadas por ahora. '
                        'Cuando un administrador te asigne una, aparecerá aquí.',
                        textAlign: TextAlign.center,
                      ),
                    )
                  else if (_filteredActivities.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(32),
                      child: Text(
                        'Ninguna actividad coincide con los filtros elegidos.',
                        textAlign: TextAlign.center,
                      ),
                    ),
                  for (final activity in _filteredActivities)
                    ListTile(
                      leading: _downloadingActivityId == activity.id
                          ? const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Icon(Icons.assignment_outlined),
                      title: Text(activity.surveyName),
                      subtitle: Text(
                        '${activity.parishName} · Del ${_formatDate(activity.initDate)} '
                        'al ${_formatDate(activity.finishDate)}',
                      ),
                      onTap: _downloadingActivityId == null
                          ? () => _openActivity(activity)
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
