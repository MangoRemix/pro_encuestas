<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['person_id','question_id','answer_id',])]

class Result extends Model
{
    //

    public function persons(): BelongsTo {
        
        return $this->belongsTo(Person::class);

    }

    public function questions(): BelongsTo {
        
        return $this->belongsTo(Question::class);

    }

    public function answers(): BelongsTo {
        
        return $this->belongsTo(Answer::class);

    }

}
