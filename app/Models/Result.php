<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['person_id', 'question_id', 'answer_id', 'pollster_id'])]

class Result extends Model
{
    use HasFactory, SoftDeletes;

    public function persons(): BelongsTo
    {

        return $this->belongsTo(Person::class);

    }

    public function questions(): BelongsTo
    {

        return $this->belongsTo(Question::class);

    }

    public function answers(): BelongsTo
    {

        return $this->belongsTo(Answer::class);

    }

    public function pollster(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'pollster_id');
    }
}
