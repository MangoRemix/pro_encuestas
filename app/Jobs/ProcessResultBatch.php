<?php

namespace App\Jobs;

use App\Models\Result;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcessResultBatch implements ShouldQueue
{
    use Queueable;

    public function __construct(public array $results, public string $batchId) {}
    
    public function handle(): void
    {
        if (empty($this->results)) {
            return;
        }

        $report = [];
        
        foreach ($this->results as $index => $surveyResults) {
            try {
                DB::transaction(function () use ($surveyResults) {
                    foreach ($surveyResults as $item) {
                        Result::create([
                            'person_id'   => $item['person_id'],
                            'question_id' => $item['question_id'],
                            'answer_id'   => $item['answer_id'],
                            'pollster_id' => $item['pollster_id'],
                        ]);
                    }
                });
                $report[$index] = 'GUARDADA';
            } catch (\Throwable $th) {
                $report[$index] = 'FALLIDO';
            }
        }
        
        Cache::put("batch_status_{$this->batchId}", $report, 3600);
    }
}
