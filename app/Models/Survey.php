<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable(['name', 'init_date', 'finish_date', 'parish_id'])]

class Survey extends Model
{
    //
    use HasFactory, SoftDeletes;

    public function categories(): HasMany
    {

        return $this->hasMany(Category::class);

    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(SurveyRun::class)->orderByDesc('init_date');
    }

    public function assignedPollsters(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'survey_person')
            ->using(SurveyPerson::class)
            ->withPivot(['assigned_by', 'assigned_at', 'unassigned_at', 'unassigned_by'])
            ->withTimestamps();
    }

    /**
     * Solo los encuestadores con una asignación activa (sin desasignar) a
     * esta encuesta.
     */
    public function activePollsters(): BelongsToMany
    {
        return $this->assignedPollsters()->wherePivotNull('unassigned_at');
    }

    /**
     * La encuesta está dentro de su jornada vigente ahora mismo. Se calcula
     * en cada consulta (sin columna de estado ni cron) para que "ocultarse"
     * al vencer el periodo sea automático.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('init_date', '<=', $now)->where('finish_date', '>=', $now);
    }
}
