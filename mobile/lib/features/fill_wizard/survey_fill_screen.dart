import 'package:drift/drift.dart' as drift;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/db/app_database.dart';
import '../../core/providers.dart';

class SurveyFillScreen extends ConsumerStatefulWidget {
  const SurveyFillScreen({super.key, required this.instanceUuid});

  final String instanceUuid;

  @override
  ConsumerState<SurveyFillScreen> createState() => _SurveyFillScreenState();
}

class _SurveyFillScreenState extends ConsumerState<SurveyFillScreen> {
  SurveyInstance? _instance;
  List<CachedCategory> _categories = [];
  final Map<int, List<CachedQuestion>> _questionsByCategory = {};
  final Map<int, List<CachedAnswer>> _answersByQuestion = {};
  Set<int> _selectedAnswerIdsFlat = {};
  int _categoryIndex = 0;
  bool _loading = true;

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

    final categories = await _db.categoriesForSurvey(instance.surveyId);

    for (final category in categories) {
      final questions = await _db.questionsForCategory(category.id);
      _questionsByCategory[category.id] = questions;

      for (final question in questions) {
        _answersByQuestion[question.id] = await _db.answersForQuestion(question.id);
      }
    }

    final resumeIndex = instance.currentCategoryId == null
        ? 0
        : categories.indexWhere((c) => c.id == instance.currentCategoryId);

    await _loadSelectedAnswers();

    if (mounted) {
      setState(() {
        _instance = instance;
        _categories = categories;
        _categoryIndex = resumeIndex < 0 ? 0 : resumeIndex;
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

  bool _categoryIsComplete(int categoryIndex) {
    final questions = _questionsByCategory[_categories[categoryIndex].id] ?? [];

    return questions.every((q) => (_answersByQuestion[q.id] ?? [])
        .any((a) => _selectedAnswerIdsFlat.contains(a.id)));
  }

  bool get _allCategoriesComplete =>
      List.generate(_categories.length, (i) => i).every(_categoryIsComplete);

  Future<void> _goToCategory(int index) async {
    setState(() => _categoryIndex = index);

    await _db.upsertInstance(SurveyInstancesCompanion(
      localUuid: drift.Value(widget.instanceUuid),
      surveyId: drift.Value(_instance!.surveyId),
      pollsterPersonId: drift.Value(_instance!.pollsterPersonId),
      currentCategoryId: drift.Value(_categories[index].id),
      updatedAt: drift.Value(DateTime.now()),
    ));
  }

  Future<void> _finish() async {
    if (!_allCategoriesComplete) {
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
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text('Encuesta guardada. Encuestados en esta encuesta: $total'),
      ));
      context.replace('/respondent/${_instance!.surveyId}');
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

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    if (_categories.isEmpty) {
      return const Scaffold(
        body: Center(child: Text('Esta encuesta no tiene categorías cargadas.')),
      );
    }

    final category = _categories[_categoryIndex];
    final questions = _questionsByCategory[category.id] ?? [];
    final isLastCategory = _categoryIndex == _categories.length - 1;

    return Scaffold(
      appBar: AppBar(
        title: Text(category.name),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(4),
          child: LinearProgressIndicator(
            value: (_categoryIndex + 1) / _categories.length,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          for (final question in questions) ...[
            Padding(
              padding: const EdgeInsets.only(top: 12, bottom: 4),
              child: Text(
                question.name,
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
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
        ],
      ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              if (_categoryIndex > 0)
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => _goToCategory(_categoryIndex - 1),
                    child: const Text('Anterior'),
                  ),
                ),
              if (_categoryIndex > 0) const SizedBox(width: 12),
              Expanded(
                child: FilledButton(
                  onPressed: isLastCategory
                      ? _finish
                      : () => _goToCategory(_categoryIndex + 1),
                  child: Text(isLastCategory ? 'Finalizar' : 'Siguiente'),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
