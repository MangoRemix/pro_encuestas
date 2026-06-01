<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name','order','category_id'])]

class Question extends Model
{
    //
    use  HasFactory,SoftDeletes;

    protected static function booted()
    {
        static::deleted(function ($question) {
            // Esto buscará todos los resultados asociados y aplicará softDelete
            $question->results()->delete();
        });

        static::restored(function ($question) {
            // Opcional: Si restauras la respuesta, podrías restaurar los resultados
            $question->results()->restore();
        });
    }
    
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
