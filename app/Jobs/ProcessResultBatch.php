<?php

namespace App\Jobs;

use App\Models\Result;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ProcessResultBatch implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public array $results, public string $batchId) {}
    
    public function handle(): void
    {
        if (empty($this->results)) {
            return;
        }

        $report = [];
        
        foreach ($this->results as $index => $surveys) {
            foreach($surveys as $index2 => $survey){
                try {
                    $survey['created_at'] = now();
                    $survey['updated_at'] = now();
                    Result::insert($survey);
                    $report[$index] = 'GUARDADA';
                } catch (\Throwable $th) {
                    $report[$index] = 'FALLIDO: ' . $th->getMessage();
                }
            }
            
        }
        
        Cache::put("batch_status_{$this->batchId}", $report, 3600);
    }
}

