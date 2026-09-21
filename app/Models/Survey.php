<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

#[Fillable(['name'])]

class Survey extends Model
{
    //
    use HasFactory, SoftDeletes;

    public function categories(): HasMany
    {

        return $this->hasMany(Category::class);

    }

    /**
     * Actividades de campo (parroquia + rango de fechas + encuestador(es))
     * en las que se aplica esta encuesta. Una encuesta puede tener muchas.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderByDesc('init_date');
    }

    /**
     * Ya tiene datos recolectados (al menos un Result vía
     * Result -> Question -> Category -> Survey). Una vez que esto es true,
     * la estructura (categorías/preguntas/respuestas) de la encuesta ya no
     * debe poder modificarse: cambiarla rompería la interpretación de los
     * reportes históricos.
     */
    public function hasResults(): bool
    {
        return DB::table('results')
            ->join('questions', 'questions.id', '=', 'results.question_id')
            ->join('categories', 'categories.id', '=', 'questions.category_id')
            ->where('categories.survey_id', $this->id)
            ->exists();
    }
}
