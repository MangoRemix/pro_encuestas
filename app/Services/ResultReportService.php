<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Result;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;

class ResultReportService
{
    /**
     * Aplica los filtros opcionales de actividad puntual, encuestador y/o
     * rango de fechas de recolección (results.created_at) a un query
     * builder que ya tiene 'results' en el FROM. Compartido por todos los
     * métodos de reporte para que estos filtros se comporten igual en
     * cualquier gráfica/tabla. El filtro de parroquia (del encuestado) no
     * vive acá porque requiere un join a 'persons' que no todos los
     * queries tienen listo — cada método lo agrega por su cuenta.
     */
    private function applyActivityAndDateFilters($query, ?int $activityId, ?string $dateFrom, ?string $dateTo, string $alias = 'results', ?int $pollsterId = null)
    {
        if ($activityId) {
            $query->where("{$alias}.activity_id", $activityId);
        }

        if ($pollsterId) {
            $query->where("{$alias}.pollster_id", $pollsterId);
        }

        if ($dateFrom) {
            $query->whereDate("{$alias}.created_at", '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate("{$alias}.created_at", '<=', $dateTo);
        }

        return $query;
    }

    public function getActivitiesForSurvey(int $surveyId)
    {
        return Activity::query()
            ->where('survey_id', $surveyId)
            ->with('parishes')
            ->orderByDesc('init_date')
            ->get();
    }

    public function getRespondentCountByAgeRange(int $surveyId, ?int $min, ?int $max, ?int $activityId = null, ?int $parishId = null, ?int $pollsterId = null, ?string $dateFrom = null, ?string $dateTo = null): int
    {
        $query = Result::query()
            ->join('persons as p', 'results.person_id', '=', 'p.id')
            ->join('questions as q', 'results.question_id', '=', 'q.id')
            ->join('categories as c', 'q.category_id', '=', 'c.id')
            ->where('c.survey_id', (int) $surveyId);

        $this->applyActivityAndDateFilters($query, $activityId, $dateFrom, $dateTo, 'results', $pollsterId);

        if ($parishId) {
            $query->where('p.parish_id', $parishId);
        }

        if ($min !== null) {
            $query->where('p.age', '>=', $min);
        }

        if ($max !== null) {
            $query->where('p.age', '<=', $max);
        }

        return $query->distinct('results.person_id')->count('results.person_id');
    }

    public function reportCountAnswersByQuestion(int $surveyId, ?int $categoryId, ?int $activityId = null, ?int $parishId = null, ?int $pollsterId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $query = Result::query()
            ->join('questions as q', 'q.id', '=', 'results.question_id')
            ->join('categories as c', 'c.id', '=', 'q.category_id')
            ->join('surveys as s', 's.id', '=', 'c.survey_id')
            ->join('answers as a', 'a.id', '=', 'results.answer_id')
            ->join('persons as p', 'p.id', '=', 'results.person_id')
            ->select([
                'results.question_id',
                'q.name as question_name',
                'results.answer_id',
                'a.name as answer_name',
                'c.name as category_name',
                DB::raw('count(results.answer_id) as total'),
            ])
            ->orderBy('c.id', 'ASC')
            ->orderBy('total', 'DESC')
            ->where('s.id', $surveyId);

        $this->applyActivityAndDateFilters($query, $activityId, $dateFrom, $dateTo, 'results', $pollsterId);

        if ($parishId) {
            $query->where('p.parish_id', $parishId);
        }

        if ($categoryId) {
            $query->where('c.id', $categoryId);
        }

        return $query->groupBy([
            'results.question_id',
            'q.name',
            'results.answer_id',
            'a.name',
            'c.id',
        ])->get();
    }

    public function getSurveyReportStructure(int $id, ?int $activityId = null, ?int $parishId = null, ?int $pollsterId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $survey = Survey::with([
            'categories' => fn ($query) => $query->orderBy('order', 'asc'),
            'categories.questions' => fn ($query) => $query->orderBy('order', 'asc'),
            'categories.questions.answers' => fn ($query) => $query->orderBy('order', 'asc'),
        ])->findOrFail($id);

        $totalRespondent = DB::query()
            ->fromSub(function ($query) use ($id, $activityId, $parishId, $pollsterId, $dateFrom, $dateTo) {
                $query->from('results as r')
                    ->leftJoin('questions as q', 'q.id', '=', 'r.question_id')
                    ->leftJoin('categories as c', 'c.id', '=', 'q.category_id')
                    ->leftJoin('surveys as s', 's.id', '=', 'c.survey_id')
                    ->leftJoin('persons as p', 'p.id', '=', 'r.person_id')
                    ->where('s.id', $id)
                    ->select('r.person_id')
                    ->groupBy('r.person_id');

                $this->applyActivityAndDateFilters($query, $activityId, $dateFrom, $dateTo, 'r', $pollsterId);

                if ($parishId) {
                    $query->where('p.parish_id', $parishId);
                }
            }, 'sub')
            ->count();

        $survey->total_respondent = $totalRespondent;

        $answersCountQuery = Result::query()
            ->join('questions as q', 'q.id', '=', 'results.question_id')
            ->join('categories as c', 'c.id', '=', 'q.category_id')
            ->join('surveys as s', 's.id', '=', 'c.survey_id')
            ->rightJoin('answers as a', 'a.id', '=', 'results.answer_id')
            ->leftJoin('persons as p', 'p.id', '=', 'results.person_id')
            ->where('s.id', $id)
            ->select([
                'results.question_id',
                'results.answer_id',
                DB::raw('count(results.id) as total_votes'),
            ]);

        $this->applyActivityAndDateFilters($answersCountQuery, $activityId, $dateFrom, $dateTo, 'results', $pollsterId);

        if ($parishId) {
            $answersCountQuery->where('p.parish_id', $parishId);
        }

        $answersCount = $answersCountQuery
            ->groupBy('results.question_id', 'results.answer_id')
            ->get()
            ->keyBy(fn ($item) => $item->question_id.'-'.$item->answer_id);

        foreach ($survey->categories as $category) {
            foreach ($category->questions as $question) {
                foreach ($question->answers as $answer) {
                    $key = $question->id.'-'.$answer->id;
                    $answer->total_votes = isset($answersCount[$key]) ? (int) $answersCount[$key]->total_votes : 0;
                }
            }
        }

        return $survey;
    }

    public function getRespondentCountBySex(int $surveyId, ?int $sexId, ?int $activityId = null, ?int $parishId = null, ?int $pollsterId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $sql = '
            SELECT
                p.sex_id,
                COUNT(DISTINCT r.person_id) as total_respondents
            FROM results r
            JOIN questions q ON q.id = r.question_id
            JOIN categories c ON c.id = q.category_id
            JOIN persons p ON p.id = r.person_id
            WHERE c.survey_id = :survey_id
        ';

        $bindings = ['survey_id' => $surveyId];

        if (! empty($sexId)) {
            $sql .= ' AND p.sex_id = :sex_id';
            $bindings['sex_id'] = (int) $sexId;
        }

        if (! empty($parishId)) {
            $sql .= ' AND p.parish_id = :parish_id';
            $bindings['parish_id'] = (int) $parishId;
        }

        if (! empty($pollsterId)) {
            $sql .= ' AND r.pollster_id = :pollster_id';
            $bindings['pollster_id'] = (int) $pollsterId;
        }

        if (! empty($activityId)) {
            $sql .= ' AND r.activity_id = :activity_id';
            $bindings['activity_id'] = (int) $activityId;
        }

        if (! empty($dateFrom)) {
            $sql .= ' AND r.created_at >= :date_from';
            $bindings['date_from'] = $dateFrom.' 00:00:00';
        }

        if (! empty($dateTo)) {
            $sql .= ' AND r.created_at <= :date_to';
            $bindings['date_to'] = $dateTo.' 23:59:59';
        }

        $sql .= ' GROUP BY p.sex_id ORDER BY p.sex_id ASC';

        return DB::select($sql, $bindings);
    }

    public function getRespondentCountByParish(int $surveyId, ?int $parishId, ?int $activityId = null, ?int $pollsterId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $sql = '
            SELECT
                p.parish_id,
                COUNT(DISTINCT r.person_id) as total_respondents
            FROM results r
            JOIN questions q ON q.id = r.question_id
            JOIN categories c ON c.id = q.category_id
            JOIN persons p ON p.id = r.person_id
            WHERE c.survey_id = :survey_id
        ';

        $bindings = ['survey_id' => $surveyId];

        if (! empty($parishId)) {
            $sql .= ' AND p.parish_id = :parish_id';
            $bindings['parish_id'] = (int) $parishId;
        }

        if (! empty($pollsterId)) {
            $sql .= ' AND r.pollster_id = :pollster_id';
            $bindings['pollster_id'] = (int) $pollsterId;
        }

        if (! empty($activityId)) {
            $sql .= ' AND r.activity_id = :activity_id';
            $bindings['activity_id'] = (int) $activityId;
        }

        if (! empty($dateFrom)) {
            $sql .= ' AND r.created_at >= :date_from';
            $bindings['date_from'] = $dateFrom.' 00:00:00';
        }

        if (! empty($dateTo)) {
            $sql .= ' AND r.created_at <= :date_to';
            $bindings['date_to'] = $dateTo.' 23:59:59';
        }

        $sql .= ' GROUP BY p.parish_id ORDER BY p.parish_id ASC';

        return DB::select($sql, $bindings);
    }

    /**
     * Para una actividad específica: cuántas encuestas (personas distintas)
     * subió cada encuestador asignado a ella, incluyendo a los que aún no
     * han subido ninguna (0). "Asignado" incluye historial completo (ver
     * Activity::assignedPollsters) — no solo los activos ahora mismo.
     */
    public function getPollsterCountsForActivity(int $activityId)
    {
        $sql = '
            SELECT
                per.id as pollster_id,
                per.name as pollster_name,
                COUNT(DISTINCT r.person_id) as total_surveys_conducted
            FROM (
                SELECT DISTINCT person_id
                FROM activity_person
                WHERE activity_id = :activity_id
            ) ap
            JOIN persons per ON per.id = ap.person_id
            LEFT JOIN results r ON r.pollster_id = per.id AND r.activity_id = :activity_id_2
            GROUP BY per.id, per.name
            ORDER BY total_surveys_conducted DESC, per.name ASC
        ';

        return DB::select($sql, [
            'activity_id' => $activityId,
            'activity_id_2' => $activityId,
        ]);
    }

    public function getTopPollsters(?int $surveyId, ?int $activityId = null, ?string $dateFrom = null, ?string $dateTo = null)
    {
        $sql = '
            SELECT
                r.pollster_id,
                p.name as pollster_name,
                COUNT(DISTINCT r.person_id) as total_surveys_conducted
            FROM results r
            JOIN persons p ON p.id = r.pollster_id
        ';

        $bindings = [];
        $wheres = [];

        if (! empty($surveyId)) {
            $sql .= '
                JOIN questions q ON q.id = r.question_id
                JOIN categories c ON c.id = q.category_id
            ';
            $wheres[] = 'c.survey_id = :survey_id';
            $bindings['survey_id'] = (int) $surveyId;
        }

        if (! empty($activityId)) {
            $wheres[] = 'r.activity_id = :activity_id';
            $bindings['activity_id'] = (int) $activityId;
        }

        if (! empty($dateFrom)) {
            $wheres[] = 'r.created_at >= :date_from';
            $bindings['date_from'] = $dateFrom.' 00:00:00';
        }

        if (! empty($dateTo)) {
            $wheres[] = 'r.created_at <= :date_to';
            $bindings['date_to'] = $dateTo.' 23:59:59';
        }

        if ($wheres) {
            $sql .= ' WHERE '.implode(' AND ', $wheres);
        }

        $sql .= ' GROUP BY r.pollster_id, p.name ORDER BY total_surveys_conducted DESC LIMIT 5';

        return DB::select($sql, $bindings);
    }
}
