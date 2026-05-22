<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name','order','question_id'])]

class Answer extends Model
{
    //

    public function questions(): BelongsTo {
        
        return $this->belongsTo(Question::class);

    }

    public function results(): HasMany {
        
        return $this->hasMany(Result::class);

    }

}
