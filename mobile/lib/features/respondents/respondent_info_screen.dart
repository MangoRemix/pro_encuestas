import 'package:drift/drift.dart' show Value;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:uuid/uuid.dart';

import '../../core/db/app_database.dart';
import '../../core/providers.dart';
import '../auth/auth_controller.dart';
import '../auth/auth_state.dart';

/// Captura los datos básicos del encuestado (parroquia/sexo/edad) y crea una
/// nueva instancia local de encuesta con estado "incompleta". Es el punto al
/// que siempre se vuelve después de terminar una encuesta, para encuestar de
/// inmediato a la siguiente persona.
class RespondentInfoScreen extends ConsumerStatefulWidget {
  const RespondentInfoScreen({super.key, required this.surveyId});

  final int surveyId;

  @override
  ConsumerState<RespondentInfoScreen> createState() =>
      _RespondentInfoScreenState();
}

class _RespondentInfoScreenState extends ConsumerState<RespondentInfoScreen> {
  final _formKey = GlobalKey<FormState>();
  final _ageController = TextEditingController();
  int? _sexId;
  int? _parishId;
  bool _creating = false;

  @override
  void dispose() {
    _ageController.dispose();
    super.dispose();
  }

  Future<void> _startSurvey() async {
    if (!_formKey.currentState!.validate() || _sexId == null || _parishId == null) {
      return;
    }

    setState(() => _creating = true);

    final db = ref.read(appDatabaseProvider);
    final authState = ref.read(authControllerProvider);
    final pollsterId = authState is AuthAuthenticated ? authState.personId : 0;

    final categories = await db.categoriesForSurvey(widget.surveyId);
    final firstCategoryId = categories.isNotEmpty ? categories.first.id : null;

    final instanceUuid = const Uuid().v4();

    await db.upsertInstance(SurveyInstancesCompanion.insert(
      localUuid: instanceUuid,
      surveyId: widget.surveyId,
      pollsterPersonId: pollsterId,
      respondentSexId: Value(_sexId),
      respondentAge: Value(int.parse(_ageController.text)),
      respondentParishId: Value(_parishId),
      currentCategoryId: Value(firstCategoryId),
    ));

    if (mounted) {
      context.replace('/fill/$instanceUuid');
    }
  }

  @override
  Widget build(BuildContext context) {
    final db = ref.watch(appDatabaseProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Datos del encuestado')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              FutureBuilder<List<CachedSex>>(
                future: db.allSexes(),
                builder: (context, snapshot) {
                  final sexes = snapshot.data ?? [];

                  return DropdownButtonFormField<int>(
                    initialValue: _sexId,
                    decoration: const InputDecoration(labelText: 'Sexo'),
                    items: [
                      for (final sex in sexes)
                        DropdownMenuItem(value: sex.id, child: Text(sex.description)),
                    ],
                    onChanged: (value) => setState(() => _sexId = value),
                    validator: (value) => value == null ? 'Selecciona el sexo' : null,
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
              const SizedBox(height: 16),
              FutureBuilder<List<CachedParish>>(
                future: db.allParishes(),
                builder: (context, snapshot) {
                  final parishes = snapshot.data ?? [];

                  return DropdownButtonFormField<int>(
                    initialValue: _parishId,
                    decoration: const InputDecoration(labelText: 'Parroquia'),
                    items: [
                      for (final parish in parishes)
                        DropdownMenuItem(value: parish.id, child: Text(parish.name)),
                    ],
                    onChanged: (value) => setState(() => _parishId = value),
                    validator: (value) =>
                        value == null ? 'Selecciona la parroquia' : null,
                  );
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
