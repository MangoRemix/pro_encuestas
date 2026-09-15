<?php

namespace App\Jobs;

use App\Models\Result;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                        // updateOrCreate: hace el job idempotente ante reintentos
                        // de la cola o doble envío del cliente.
                        Result::updateOrCreate(
                            [
                                'person_id' => $item['person_id'],
                                'question_id' => $item['question_id'],
                            ],
                            [
                                'answer_id' => $item['answer_id'],
                                'pollster_id' => $item['pollster_id'],
                            ]
                        );
                    }
                });
                $report[$index] = 'GUARDADA';
            } catch (\Throwable $th) {
                Log::error('Fallo al procesar lote de resultados', [
                    'batch_id' => $this->batchId,
                    'index' => $index,
                    'exception' => $th,
                ]);
                $report[$index] = 'FALLIDO';
            }
        }

        Cache::put("batch_status_{$this->batchId}", $report, 3600);
    }
}
