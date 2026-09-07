<?php

namespace App\Services;

use App\Models\Result;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;

class ResultReportService
{
    public function getRespondentCountByAgeRange(int $surveyId, ?int $min, ?int $max): int
    {
        $query = Result::query()
            ->join('persons as p', 'results.person_id', '=', 'p.id')
            ->join('questions as q', 'results.question_id', '=', 'q.id')
            ->join('categories as c', 'q.category_id', '=', 'c.id')
            ->where('c.survey_id', (int)$surveyId);

        if ($min !== null) {
            $query->where('p.age', '>=', $min);
        }
        
        if ($max !== null) {
            $query->where('p.age', '<=', $max);
        }

        return $query->distinct('results.person_id')->count('results.person_id');
    }

    public function reportCountAnswersByQuestion(int $surveyId, ?int $categoryId)
    {
        $query = Result::query()
            ->join('questions as q', 'q.id', '=', 'results.question_id')
            ->join('categories as c', 'c.id', '=', 'q.category_id')
            ->join('surveys as s', 's.id', '=', 'c.survey_id')
            ->join('answers as a', 'a.id', '=', 'results.answer_id')
            ->select([
                'results.question_id',
                'q.name as question_name',
                'results.answer_id',
                'a.name as answer_name',
                'c.name as category_name',
                DB::raw('count(results.answer_id) as total')
            ])
            ->orderBy('c.id', 'ASC')
            ->orderBy('total', 'DESC')
            ->where('s.id', $surveyId);

        if ($categoryId) {
            $query->where('c.id', $categoryId);
        }

        return $query->groupBy([
            'results.question_id',
            'q.name',
            'results.answer_id',
            'a.name',
            'c.id'
        ])->get();
    }

    public function getSurveyReportStructure(int $id)
    {
        $survey = Survey::with([
            'categories' => fn($query) => $query->orderBy('order', 'asc'),
            'categories.questions' => fn($query) => $query->orderBy('order', 'asc'),
            'categories.questions.answers' => fn($query) => $query->orderBy('order', 'asc'),
        ])->findOrFail($id);

        $totalRespondent = DB::query()
            ->fromSub(function ($query) use ($id) {
                $query->from('results as r')
                    ->leftJoin('questions as q', 'q.id', '=', 'r.question_id')
                    ->leftJoin('categories as c', 'c.id', '=', 'q.category_id')
                    ->leftJoin('surveys as s', 's.id', '=', 'c.survey_id')
                    ->where('s.id', $id)
                    ->select('r.person_id')
                    ->groupBy('r.person_id');
            }, 'sub')
            ->count();

        $survey->total_respondent = $totalRespondent;

        $answersCount = Result::query()
            ->join('questions as q', 'q.id', '=', 'results.question_id')
            ->join('categories as c', 'c.id', '=', 'q.category_id')
            ->join('surveys as s', 's.id', '=', 'c.survey_id')
            ->rightJoin('answers as a', 'a.id', '=', 'results.answer_id')
            ->where('s.id', $id)
            ->select([
                'results.question_id',
                'results.answer_id',
                DB::raw('count(results.id) as total_votes')
            ])
            ->groupBy('results.question_id', 'results.answer_id')
            ->get()
            ->keyBy(fn($item) => $item->question_id . '-' . $item->answer_id);

        foreach ($survey->categories as $category) {
            foreach ($category->questions as $question) {
                foreach ($question->answers as $answer) {
                    $key = $question->id . '-' . $answer->id;
                    $answer->total_votes = isset($answersCount[$key]) ? (int) $answersCount[$key]->total_votes : 0;
                }
            }
        }
        
        return $survey;
    }

    public function getRespondentCountBySex(int $surveyId, ?int $sexId)
    {
        $sql = "
            SELECT
                p.sex_id,
                COUNT(DISTINCT r.person_id) as total_respondents
            FROM results r
            JOIN questions q ON q.id = r.question_id
            JOIN categories c ON c.id = q.category_id
            JOIN persons p ON p.id = r.person_id
            WHERE c.survey_id = :survey_id
        ";

        $bindings = ['survey_id' => $surveyId];

        if (!empty($sexId)) {
            $sql .= " AND p.sex_id = :sex_id";
            $bindings['sex_id'] = (int) $sexId;
        }

        $sql .= " GROUP BY p.sex_id ORDER BY p.sex_id ASC";

        return DB::select($sql, $bindings);
    }

    public function getRespondentCountByParish(int $surveyId, ?int $parishId)
    {
        $sql = "
            SELECT
                p.parish_id,
                COUNT(DISTINCT r.person_id) as total_respondents
            FROM results r
            JOIN questions q ON q.id = r.question_id
            JOIN categories c ON c.id = q.category_id
            JOIN persons p ON p.id = r.person_id
            WHERE c.survey_id = :survey_id
        ";

        $bindings = ['survey_id' => $surveyId];

        if (!empty($parishId)) {
            $sql .= " AND p.parish_id = :parish_id";
            $bindings['parish_id'] = (int) $parishId;
        }

        $sql .= " GROUP BY p.parish_id ORDER BY p.parish_id ASC";

        return DB::select($sql, $bindings);
    }

    public function getTopPollsters(?int $surveyId)
    {
        $sql = "
            SELECT 
                r.pollster_id,
                p.name as pollster_name,
                COUNT(DISTINCT r.person_id) as total_surveys_conducted
            FROM results r
            JOIN persons p ON p.id = r.pollster_id
        ";

        $bindings = [];

        if (!empty($surveyId)) {
            $sql .= " 
                JOIN questions q ON q.id = r.question_id
                JOIN categories c ON c.id = q.category_id
                WHERE c.survey_id = :survey_id
            ";
            $bindings['survey_id'] = (int) $surveyId;
        }

        $sql .= " GROUP BY r.pollster_id, p.name ORDER BY total_surveys_conducted DESC LIMIT 5";

        return DB::select($sql, $bindings);
    }
}
