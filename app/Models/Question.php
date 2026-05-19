<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name','order','cateogry_id'])]

class Question extends Model
{
    //

    public function categories(): BelongsTo {
    
        return $this->belongsTo(Category::class);

    }

    public function answers(): HasMany {
    
        return $this->hasMany(Answer::class);

    }

    public function results(): HasMany {
        
        return $this->hasMany(Result::class);
        
    }

}
