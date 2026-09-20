<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyRun extends Model
{
    protected $fillable = [
        'survey_id',
        'parish_id',
        'init_date',
        'finish_date',
        'created_by',
    ];

    protected $casts = [
        'init_date' => 'datetime',
        'finish_date' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }
}
