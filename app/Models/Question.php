<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'order', 'category_id', 'allows_multiple_answers'])]

class Question extends Model
{
    //
    use HasFactory,SoftDeletes;

    protected $casts = [
        'allows_multiple_answers' => 'boolean',
    ];

    protected static function booted()
    {
        static::deleted(function ($question) {
            // Esto buscará todos los resultados asociados y aplicará softDelete
            $question->results()->delete();

            // Ocultar en cadena: una pregunta oculta no debe dejar sus
            // respuestas visibles/seleccionables.
            $question->answers()->get()->each->delete();
        });

        static::restored(function ($question) {
            // Restaurar en cadena (simétrico al ocultado).
            $question->answers()->withTrashed()->get()->each->restore();
            $question->results()->restore();
        });
    }

    public function categories(): BelongsTo
    {

        return $this->belongsTo(Category::class);

    }

    public function answers(): HasMany
    {

        return $this->hasMany(Answer::class);

    }

    public function results(): HasMany
    {

        return $this->hasMany(Result::class);

    }
}
