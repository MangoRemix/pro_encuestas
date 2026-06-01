<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name','order','question_id'])]

class Answer extends Model
{
    //
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::deleted(function ($answer) {
            // Esto buscará todos los resultados asociados y aplicará softDelete
            $answer->results()->delete();
        });

        static::restored(function ($answer) {
            // Opcional: Si restauras la respuesta, podrías restaurar los resultados
            $answer->results()->restore();
        });
    }


    public function questions(): BelongsTo {
        
        return $this->belongsTo(Question::class);

    }

    public function results(): HasMany {
        
        return $this->hasMany(Result::class);

    }

}
