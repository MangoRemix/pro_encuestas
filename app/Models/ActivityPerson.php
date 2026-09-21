<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ActivityPerson extends Pivot
{
    protected $table = 'activity_person';

    public $incrementing = true;

    protected $fillable = [
        'activity_id',
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

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
