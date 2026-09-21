<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'order', 'survey_id'])]

class Category extends Model
{
    //
    use HasFactory,SoftDeletes;

    protected static function booted()
    {
        static::deleted(function ($category) {
            // Esto buscará todos los resultados asociados y aplicará softDelete
            $category->results()->delete();

            // Ocultar en cadena: una categoría oculta no debe dejar sus
            // preguntas visibles/usables. Cada Question::delete() dispara a
            // su vez su propio hook, que cascada hacia sus answers/results.
            $category->questions()->get()->each->delete();
        });

        static::restored(function ($category) {
            // Restaurar en cadena (simétrico al ocultado): las preguntas
            // primero, para que luego category->results() (hasManyThrough
            // Question) pueda encontrarlas — el scope de SoftDeletes excluye
            // preguntas ocultas del JOIN intermedio.
            $category->questions()->withTrashed()->get()->each->restore();
            $category->results()->restore();
        });
    }

    public function surveys(): BelongsTo
    {

        return $this->belongsTo(Survey::class);

    }

    public function questions(): HasMany
    {

        return $this->hasMany(Question::class);

    }

    public function results(): HasManyThrough
    {

        return $this->hasManyThrough(Result::class, Question::class);

    }
}
