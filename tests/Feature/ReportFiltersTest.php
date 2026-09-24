<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Answer;
use App\Models\Category;
use App\Models\Parish;
use App\Models\Person;
use App\Models\Question;
use App\Models\Result;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_structure_can_be_filtered_by_respondent_parish(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $category = Category::factory()->create(['survey_id' => $survey->id]);
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $parishA = Parish::factory()->create();
        $parishB = Parish::factory()->create();
        $respondentA = Person::factory()->create(['parish_id' => $parishA->id]);
        $respondentB = Person::factory()->create(['parish_id' => $parishB->id]);

        Result::factory()->create([
            'person_id' => $respondentA->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
        ]);
        Result::factory()->create([
            'person_id' => $respondentB->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
        ]);

        $response = $this->actingAs($admin)->getJson(
            "/api/result/newReportStructure/{$survey->id}?parish_id={$parishA->id}"
        );

        $response->assertOk();
        $this->assertSame(1, $response->json('total_respondent'));
    }

    public function test_report_structure_can_be_filtered_by_pollster(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $category = Category::factory()->create(['survey_id' => $survey->id]);
        $question = Question::factory()->create(['category_id' => $category->id]);
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        $pollsterA = Person::factory()->create();
        $pollsterB = Person::factory()->create();

        Result::factory()->create([
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollsterA->id,
        ]);
        Result::factory()->create([
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $pollsterB->id,
        ]);

        $response = $this->actingAs($admin)->getJson(
            "/api/result/newReportStructure/{$survey->id}?pollster_id={$pollsterA->id}"
        );

        $response->assertOk();
        $this->assertSame(1, $response->json('total_respondent'));
    }

    public function test_pollster_counts_for_activity_lists_every_assigned_pollster_including_zero(): void
    {
        $admin = Person::factory()->admin()->create();
        $activePollster = Person::factory()->create(['name' => 'ENCUESTADOR ACTIVO']);
        $idlePollster = Person::factory()->create(['name' => 'ENCUESTADOR SIN SUBIDAS']);
        $activity = Activity::factory()->create();

        $activity->assignedPollsters()->attach($activePollster->id, [
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);
        $activity->assignedPollsters()->attach($idlePollster->id, [
            'assigned_by' => $admin->id,
            'assigned_at' => now(),
        ]);

        $respondentOne = Person::factory()->create();
        $respondentTwo = Person::factory()->create();
        $question = Question::factory()->create();
        $answer = Answer::factory()->create(['question_id' => $question->id]);

        Result::factory()->create([
            'person_id' => $respondentOne->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $activePollster->id,
            'activity_id' => $activity->id,
        ]);
        Result::factory()->create([
            'person_id' => $respondentTwo->id,
            'question_id' => $question->id,
            'answer_id' => $answer->id,
            'pollster_id' => $activePollster->id,
            'activity_id' => $activity->id,
        ]);

        $response = $this->actingAs($admin)->getJson(
            "/api/result/reports/pollster-counts/{$activity->id}"
        );

        $response->assertOk();
        $byId = collect($response->json())->keyBy('pollster_id');

        $this->assertSame(2, $byId[$activePollster->id]['total_surveys_conducted']);
        $this->assertSame(0, $byId[$idlePollster->id]['total_surveys_conducted']);
    }

    public function test_pollster_counts_for_activity_is_admin_only(): void
    {
        $pollster = Person::factory()->create();
        $activity = Activity::factory()->create();

        $this->actingAs($pollster)->getJson(
            "/api/result/reports/pollster-counts/{$activity->id}"
        )->assertStatus(403);
    }

    public function test_report_structure_flags_a_parish_unrelated_to_the_selected_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create(['survey_id' => $survey->id]);
        $unrelatedParish = Parish::factory()->create();

        $response = $this->actingAs($admin)->getJson(
            "/api/result/newReportStructure/{$survey->id}?activity_id={$activity->id}&parish_id={$unrelatedParish->id}"
        );

        $response->assertStatus(404);
        $this->assertTrue($response->json('no_relation'));
    }

    public function test_report_structure_flags_a_pollster_unrelated_to_the_selected_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create(['survey_id' => $survey->id]);
        $unrelatedPollster = Person::factory()->create();

        $response = $this->actingAs($admin)->getJson(
            "/api/result/newReportStructure/{$survey->id}?activity_id={$activity->id}&pollster_id={$unrelatedPollster->id}"
        );

        $response->assertStatus(404);
        $this->assertTrue($response->json('no_relation'));
    }

    public function test_report_structure_allows_a_parish_that_is_actually_covered_by_the_activity(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create(['survey_id' => $survey->id]);
        $ownParish = $activity->parishes()->first();

        $response = $this->actingAs($admin)->getJson(
            "/api/result/newReportStructure/{$survey->id}?activity_id={$activity->id}&parish_id={$ownParish->id}"
        );

        $response->assertOk();
    }

    public function test_sex_and_parish_reports_also_flag_unrelated_filters(): void
    {
        $admin = Person::factory()->admin()->create();
        $survey = Survey::factory()->create();
        $activity = Activity::factory()->create(['survey_id' => $survey->id]);
        $unrelatedParish = Parish::factory()->create();

        $this->actingAs($admin)->getJson(
            "/api/result/sex/{$survey->id}?activity_id={$activity->id}&parish_id={$unrelatedParish->id}"
        )->assertStatus(404);

        $this->actingAs($admin)->getJson(
            "/api/result/parish/{$survey->id}?activity_id={$activity->id}&parish_id={$unrelatedParish->id}"
        )->assertStatus(404);
    }
}
