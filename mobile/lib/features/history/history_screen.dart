import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/db/app_database.dart';
import '../../core/providers.dart';
import '../sync/sync_service.dart';

class HistoryScreen extends ConsumerStatefulWidget {
  const HistoryScreen({super.key});

  @override
  ConsumerState<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends ConsumerState<HistoryScreen> {
  Map<int, String> _surveyNames = {};
  bool _syncing = false;

  @override
  void initState() {
    super.initState();
    _loadSurveyNames();
  }

  Future<void> _loadSurveyNames() async {
    final surveys = await ref.read(appDatabaseProvider).allCachedSurveys();

    if (mounted) {
      setState(() {
        _surveyNames = {for (final s in surveys) s.id: s.name};
      });
    }
  }

  Color _statusColor(String status) {
    switch (status) {
      case InstanceStatus.preparada:
        return Colors.orange;
      case InstanceStatus.completada:
        return Colors.green;
      default:
        return Colors.red;
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case InstanceStatus.preparada:
        return 'Preparada';
      case InstanceStatus.completada:
        return 'Completada';
      default:
        return 'Incompleta';
    }
  }

  Future<void> _uploadAll() async {
    setState(() => _syncing = true);

    final result = await ref.read(syncServiceProvider).uploadPending();

    if (mounted) {
      setState(() => _syncing = false);
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(
          'Subidas: ${result.uploaded}'
          '${result.failed > 0 ? ' — Fallidas: ${result.failed} (revisa la conexión)' : ''}',
        ),
      ));
    }
  }

  Future<void> _uploadOne(String uuid) async {
    final success = await ref.read(syncServiceProvider).uploadSingle(uuid);

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(success ? 'Encuesta subida.' : 'No se pudo subir. Sin conexión.'),
      ));
    }
  }

  /// Una encuesta "preparada" (completa, ya lista para subir) no se puede
  /// borrar: es el estado con más riesgo real de perder datos ya
  /// recolectados por accidente. Hay que subirla (o esperar a que se suba
  /// sola) antes de poder eliminarla.
  bool _canDelete(SurveyInstance instance) =>
      instance.status != InstanceStatus.preparada;

  Future<void> _deleteOne(SurveyInstance instance) async {
    if (!_canDelete(instance)) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text(
          'Esta encuesta ya está lista para subir; súbela antes de poder borrarla.',
        ),
      ));

      return;
    }

    final isUnsynced = instance.status != InstanceStatus.completada;
    final confirmed = await _confirm(
      isUnsynced
          ? '¿Eliminar esta encuesta? Aún no se ha subido, se perderá.'
          : '¿Eliminar esta encuesta ya subida? Solo libera espacio del teléfono.',
    );

    if (confirmed) {
      await ref.read(appDatabaseProvider).deleteInstance(instance.localUuid);
    }
  }

  /// Solo borra las ya subidas ("completada") — las "incompleta"/"preparada"
  /// quedan fuera del borrado masivo; si hace falta borrarlas se hace una
  /// por una desde su propio ícono de papelera.
  Future<void> _deleteAll(List<SurveyInstance> instances) async {
    final completed = instances
        .where((i) => i.status == InstanceStatus.completada)
        .toList();

    if (completed.isEmpty) return;

    final confirmed = await _confirm(
      '¿Eliminar todas las encuestas ya subidas? Esto no afecta las que aún no se han subido.',
    );

    if (!confirmed) return;

    final db = ref.read(appDatabaseProvider);

    for (final instance in completed) {
      await db.deleteInstance(instance.localUuid);
    }
  }

  Future<bool> _confirm(String message) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancelar'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Aceptar'),
          ),
        ],
      ),
    );

    return result ?? false;
  }

  @override
  Widget build(BuildContext context) {
    final instancesAsync = ref.watch(_instancesStreamProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Historial de encuestas'),
        actions: [
          IconButton(
            icon: _syncing
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Icon(Icons.cloud_upload_outlined),
            tooltip: 'Subir todas las pendientes',
            onPressed: _syncing ? null : _uploadAll,
          ),
          instancesAsync.when(
            data: (instances) => IconButton(
              icon: const Icon(Icons.delete_sweep_outlined),
              tooltip: 'Eliminar todas las ya subidas',
              onPressed:
                  instances.any((i) => i.status == InstanceStatus.completada)
                      ? () => _deleteAll(instances)
                      : null,
            ),
            loading: () => const SizedBox.shrink(),
            error: (_, __) => const SizedBox.shrink(),
          ),
        ],
      ),
      body: instancesAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text('Error: $error')),
        data: (instances) {
          if (instances.isEmpty) {
            return const Center(child: Text('Aún no has llenado ninguna encuesta.'));
          }

          return ListView.builder(
            itemCount: instances.length,
            itemBuilder: (context, index) {
              final instance = instances[index];

              return ListTile(
                leading: CircleAvatar(
                  backgroundColor: _statusColor(instance.status),
                  child: Text(
                    _statusLabel(instance.status)[0],
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
                title: Text(_surveyNames[instance.surveyId] ?? 'Encuesta ${instance.surveyId}'),
                subtitle: Text(_statusLabel(instance.status)),
                onTap: instance.status == InstanceStatus.incompleta
                    ? () => context.push('/fill/${instance.localUuid}')
                    : null,
                trailing: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (instance.status == InstanceStatus.preparada)
                      IconButton(
                        icon: const Icon(Icons.cloud_upload_outlined),
                        onPressed: () => _uploadOne(instance.localUuid),
                      ),
                    IconButton(
                      icon: const Icon(Icons.delete_outline),
                      tooltip: _canDelete(instance)
                          ? null
                          : 'Súbela antes de poder borrarla',
                      onPressed: _canDelete(instance)
                          ? () => _deleteOne(instance)
                          : null,
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}

final _instancesStreamProvider = StreamProvider<List<SurveyInstance>>((ref) {
  return ref.watch(appDatabaseProvider).watchAllInstances();
});
