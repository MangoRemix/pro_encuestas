import 'package:drift/drift.dart' show Value;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:uuid/uuid.dart';

import '../../core/db/app_database.dart';
import '../../core/providers.dart';
import '../auth/auth_controller.dart';
import '../auth/auth_state.dart';

/// Captura los datos básicos del encuestado (sexo/edad) y crea una nueva
/// instancia local de encuesta con estado "incompleta". Es el punto al que
/// siempre se vuelve después de terminar una encuesta, para encuestar de
/// inmediato a la siguiente persona.
///
/// La parroquia del encuestado ya no la elige el encuestador aquí: viene
/// fija por la actividad asignada (encuesta+parroquia+fechas).
class RespondentInfoScreen extends ConsumerStatefulWidget {
  const RespondentInfoScreen({super.key, required this.activityId});

  final int activityId;

  @override
  ConsumerState<RespondentInfoScreen> createState() =>
      _RespondentInfoScreenState();
}

class _RespondentInfoScreenState extends ConsumerState<RespondentInfoScreen> {
  final _formKey = GlobalKey<FormState>();
  final _ageController = TextEditingController();
  int? _sexId;
  bool _creating = false;
  CachedActivity? _activity;

  @override
  void initState() {
    super.initState();
    _loadActivity();
  }

  Future<void> _loadActivity() async {
    final db = ref.read(appDatabaseProvider);
    final activity = await db.activityById(widget.activityId);

    if (mounted) {
      setState(() => _activity = activity);
    }
  }

  @override
  void dispose() {
    _ageController.dispose();
    super.dispose();
  }

  Future<void> _startSurvey() async {
    final activity = _activity;

    if (!_formKey.currentState!.validate() || _sexId == null || activity == null) {
      return;
    }

    setState(() => _creating = true);

    final db = ref.read(appDatabaseProvider);
    final authState = ref.read(authControllerProvider);
    final pollsterId = authState is AuthAuthenticated ? authState.personId : 0;

    final categories = await db.categoriesForSurvey(activity.surveyId);
    final firstCategoryId = categories.isNotEmpty ? categories.first.id : null;

    final instanceUuid = const Uuid().v4();

    await db.upsertInstance(SurveyInstancesCompanion.insert(
      localUuid: instanceUuid,
      surveyId: activity.surveyId,
      activityId: Value(activity.id),
      pollsterPersonId: pollsterId,
      respondentSexId: Value(_sexId),
      respondentAge: Value(int.parse(_ageController.text)),
      respondentParishId: Value(activity.parishId),
      currentCategoryId: Value(firstCategoryId),
    ));

    if (mounted) {
      context.replace('/fill/$instanceUuid');
    }
  }

  @override
  Widget build(BuildContext context) {
    final db = ref.watch(appDatabaseProvider);
    final activity = _activity;

    return Scaffold(
      appBar: AppBar(title: const Text('Datos del encuestado')),
      body: activity == null
          ? const Center(child: CircularProgressIndicator())
          : Padding(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Text(
                        '${activity.surveyName} · ${activity.parishName}',
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                    ),
                    FutureBuilder<List<CachedSex>>(
                      future: db.allSexes(),
                      builder: (context, snapshot) {
                        final sexes = snapshot.data ?? [];

                        return DropdownButtonFormField<int>(
                          initialValue: _sexId,
                          decoration: const InputDecoration(labelText: 'Género'),
                          items: [
                            for (final sex in sexes)
                              DropdownMenuItem(
                                value: sex.id,
                                child: Text(sex.description),
                              ),
                          ],
                          onChanged: (value) => setState(() => _sexId = value),
                          validator: (value) =>
                              value == null ? 'Selecciona el género' : null,
                        );
                      },
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _ageController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Edad'),
                      validator: (value) {
                        final age = int.tryParse(value ?? '');

                        if (age == null || age < 0 || age > 120) {
                          return 'Ingresa una edad válida';
                        }

                        return null;
                      },
                    ),
                    const SizedBox(height: 24),
                    FilledButton(
                      onPressed: _creating ? null : _startSurvey,
                      child: _creating
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            )
                          : const Text('Comenzar encuesta'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
