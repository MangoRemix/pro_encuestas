<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name','order','survery_id'])]

class Category extends Model
{
    //

    public function surveys(): BelongsTo {

        return $this->belongsTo(Survey::class);

    }

    public function questions(): HasMany {
        
        return $this->hasMany(Question::class);
        
    }

}
