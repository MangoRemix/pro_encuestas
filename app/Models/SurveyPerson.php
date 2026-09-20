<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SurveyPerson extends Pivot
{
    protected $table = 'survey_person';

    public $incrementing = true;

    protected $fillable = [
        'survey_id',
        'person_id',
        'assigned_by',
        'assigned_at',
        'unassigned_at',
        'unassigned_by',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
