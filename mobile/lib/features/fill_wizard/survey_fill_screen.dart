import 'package:drift/drift.dart' as drift;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/db/app_database.dart';
import '../../core/providers.dart';

/// Una pregunta concreta dentro de su categoría — la unidad de navegación
/// del wizard (antes se navegaba por categoría completa; ahora se avanza
/// de a una pregunta, sin importar en qué categoría caiga cada una).
class _QuestionSlot {
  _QuestionSlot({required this.category, required this.question});

  final CachedCategory category;
  final CachedQuestion question;
}

class SurveyFillScreen extends ConsumerStatefulWidget {
  const SurveyFillScreen({super.key, required this.instanceUuid});

  final String instanceUuid;

  @override
  ConsumerState<SurveyFillScreen> createState() => _SurveyFillScreenState();
}

class _SurveyFillScreenState extends ConsumerState<SurveyFillScreen> {
  SurveyInstance? _instance;
  CachedSurvey? _survey;
  List<_QuestionSlot> _slots = [];
  final Map<int, List<CachedAnswer>> _answersByQuestion = {};
  Set<int> _selectedAnswerIdsFlat = {};
  int _slotIndex = 0;
  bool _loading = true;

  // Encuesta ya finalizada en este armado de la pantalla — se muestra una
  // vista de cierre aparte en vez de navegar de inmediato, para que quede
  // claro que sí se guardó.
  bool _completed = false;
  int _respondentCount = 0;

  AppDatabase get _db => ref.read(appDatabaseProvider);

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final instance = await _db.instanceByUuid(widget.instanceUuid);

    if (instance == null) {
      if (mounted) context.pop();

      return;
    }

    final survey = await _db.surveyById(instance.surveyId);
    final categories = await _db.categoriesForSurvey(instance.surveyId);
    final slots = <_QuestionSlot>[];

    for (final category in categories) {
      final questions = await _db.questionsForCategory(category.id);

      for (final question in questions) {
        _answersByQuestion[question.id] = await _db.answersForQuestion(question.id);
        slots.add(_QuestionSlot(category: category, question: question));
      }
    }

    final resumeIndex = instance.currentQuestionId == null
        ? 0
        : slots.indexWhere((s) => s.question.id == instance.currentQuestionId);

    await _loadSelectedAnswers();

    if (mounted) {
      setState(() {
        _instance = instance;
        _survey = survey;
        _slots = slots;
        _slotIndex = resumeIndex < 0 ? 0 : resumeIndex;
        _loading = false;
      });
    }
  }

  Future<void> _loadSelectedAnswers() async {
    final rows = await _db.answersForInstance(widget.instanceUuid);
    _selectedAnswerIdsFlat = rows.map((r) => r.answerId).toSet();
  }

  bool _isSelected(int answerId) => _selectedAnswerIdsFlat.contains(answerId);

  Future<void> _toggleAnswer(CachedQuestion question, int answerId) async {
    if (question.allowsMultipleAnswers) {
      if (_isSelected(answerId)) {
        await _db.unsetInstanceAnswer(
          instanceUuid: widget.instanceUuid,
          questionId: question.id,
          answerId: answerId,
        );
      } else {
        await _db.setInstanceAnswer(
          instanceUuid: widget.instanceUuid,
          questionId: question.id,
          answerId: answerId,
        );
      }
    } else {
      await _db.replaceSingleAnswer(
        instanceUuid: widget.instanceUuid,
        questionId: question.id,
        answerId: answerId,
      );
    }

    await _loadSelectedAnswers();

    if (mounted) setState(() {});
  }

  bool _isSlotAnswered(_QuestionSlot slot) =>
      (_answersByQuestion[slot.question.id] ?? [])
          .any((a) => _selectedAnswerIdsFlat.contains(a.id));

  /// Avanza/retrocede y guarda de inmediato en cuál pregunta quedó — si la
  /// app se cierra o el teléfono falla, se retoma exactamente aquí.
  Future<void> _goToSlot(int index) async {
    setState(() => _slotIndex = index);

    await _db.upsertInstance(SurveyInstancesCompanion(
      localUuid: drift.Value(widget.instanceUuid),
      surveyId: drift.Value(_instance!.surveyId),
      pollsterPersonId: drift.Value(_instance!.pollsterPersonId),
      currentQuestionId: drift.Value(_slots[index].question.id),
      updatedAt: drift.Value(DateTime.now()),
    ));
  }

  Future<void> _finish() async {
    if (!_slots.every(_isSlotAnswered)) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
        content: Text('Todas las preguntas deben tener al menos una respuesta.'),
      ));

      return;
    }

    await _db.upsertInstance(SurveyInstancesCompanion(
      localUuid: drift.Value(widget.instanceUuid),
      surveyId: drift.Value(_instance!.surveyId),
      pollsterPersonId: drift.Value(_instance!.pollsterPersonId),
      status: const drift.Value(InstanceStatus.preparada),
      updatedAt: drift.Value(DateTime.now()),
    ));

    final total = await _countRespondents(_instance!.surveyId);

    if (mounted) {
      setState(() {
        _completed = true;
        _respondentCount = total;
      });
    }
  }

  Future<int> _countRespondents(int surveyId) async {
    final all = await _db.watchInstancesForSurvey(surveyId).first;

    return all
        .where((i) =>
            i.status == InstanceStatus.preparada ||
            i.status == InstanceStatus.completada)
        .length;
  }

  // pushReplacement (no go): go() vacía toda la pila de navegación, lo que
  // dejaba sin nada a qué volver — el botón atrás del teléfono terminaba
  // cerrando la app en vez de regresar a "Actividades asignadas".
  void _startNextRespondent() {
    final activityId = _instance?.activityId;

    if (activityId != null) {
      context.pushReplacement('/respondent/$activityId');
    } else {
      // Instancia sin actividad asociada (creada antes de que existiera la
      // columna) — no hay a qué actividad volver, se manda al listado.
      context.pushReplacement('/surveys');
    }
  }

  void _goHome() => context.go('/surveys');

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_completed) {
      return Scaffold(
        appBar: AppBar(title: const Text('Encuesta completada')),
        body: SafeArea(
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.check_circle, color: Colors.green, size: 96),
                  const SizedBox(height: 24),
                  Text(
                    'Encuesta completada',
                    style: Theme.of(context).textTheme.headlineSmall,
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    'Encuestados en esta actividad: $_respondentCount',
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 32),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton(
                      onPressed: _startNextRespondent,
                      child: const Text('Encuestar a otra persona'),
                    ),
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton(
                      onPressed: _goHome,
                      child: const Text('Volver al inicio'),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      );
    }

    if (_slots.isEmpty) {
      return const Scaffold(
        body: Center(child: Text('Esta encuesta no tiene preguntas cargadas.')),
      );
    }

    final slot = _slots[_slotIndex];
    final question = slot.question;
    final isLastSlot = _slotIndex == _slots.length - 1;
    final answered = _isSlotAnswered(slot);

    return Scaffold(
      appBar: AppBar(
        title: Text(_survey?.name ?? ''),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: LinearProgressIndicator(
            value: (_slotIndex + 1) / _slots.length,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Text(
            slot.category.name,
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
                  color: Theme.of(context).colorScheme.primary,
                ),
          ),
          const SizedBox(height: 4),
          Text(
            question.name,
            style: Theme.of(context).textTheme.titleLarge,
          ),
          const SizedBox(height: 12),
          if (question.allowsMultipleAnswers)
            for (final answer in _answersByQuestion[question.id] ?? [])
              CheckboxListTile(
                title: Text(answer.name),
                value: _isSelected(answer.id),
                onChanged: (_) => _toggleAnswer(question, answer.id),
              )
          else
            RadioGroup<int>(
              groupValue: (_answersByQuestion[question.id] ?? [])
                  .map((a) => a.id)
                  .firstWhere(_isSelected, orElse: () => -1),
              onChanged: (value) {
                if (value != null) _toggleAnswer(question, value);
              },
              child: Column(
                children: [
                  for (final answer in _answersByQuestion[question.id] ?? [])
                    RadioListTile<int>(
                      title: Text(answer.name),
                      value: answer.id,
                    ),
                ],
              ),
            ),
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              if (_slotIndex > 0)
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _goToSlot(_slotIndex - 1),
                    child: const Text('Anterior'),
                  ),
                ),
              if (_slotIndex > 0) const SizedBox(width: 12),
              Expanded(
                child: FilledButton(
                  onPressed: !answered
                      ? null
                      : isLastSlot
                          ? _finish
                          : () => _goToSlot(_slotIndex + 1),
                  child: Text(isLastSlot ? 'Finalizar' : 'Siguiente'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
