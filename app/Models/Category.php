<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name','order','survey_id'])]

class Category extends Model
{
    //
    use SoftDeletes;
    
    public function surveys(): BelongsTo {

        return $this->belongsTo(Survey::class);

    }

    public function questions(): HasMany {
        
        return $this->hasMany(Question::class);
        
    }

}
