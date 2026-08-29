<?php

namespace App\Jobs;

use App\Http\Controllers\SurveyImportController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImportSurveyExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $filePath,
        protected string $batchId,
        protected string $initDate,
        protected string $finishDate
    ) {}

    public function handle(): void
    {
        try {
            (new SurveyImportController)->processExcelFile(
                Storage::path($this->filePath),
                $this->initDate,
                $this->finishDate
            );
            Cache::put("batch_status_{$this->batchId}", ['finished' => true, 'status' => 'success'], 3600);
        } catch (\Throwable $e) {
            Cache::put("batch_status_{$this->batchId}", ['finished' => true, 'status' => 'error', 'message' => $e->getMessage()], 3600);
        } finally {
            Storage::delete($this->filePath);
        }
    }
}

