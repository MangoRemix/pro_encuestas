<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['survey_id', 'parish_id', 'init_date', 'finish_date', 'created_by'])]

class Activity extends Model
{
    use HasFactory;

    protected $casts = [
        'init_date' => 'datetime',
        'finish_date' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(Parish::class);
    }

    public function assignedPollsters(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'activity_person')
            ->using(ActivityPerson::class)
            ->withPivot(['assigned_by', 'assigned_at', 'unassigned_at', 'unassigned_by'])
            ->withTimestamps();
    }

    /**
     * Solo los encuestadores con una asignación activa (sin desasignar) a
     * esta actividad.
     */
    public function activePollsters(): BelongsToMany
    {
        return $this->assignedPollsters()->wherePivotNull('unassigned_at');
    }

    /**
     * La actividad está dentro de su rango de fechas vigente ahora mismo.
     * Se calcula en cada consulta (sin columna de estado ni cron) para que
     * "vencerse" sea automático.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('init_date', '<=', $now)->where('finish_date', '>=', $now);
    }

    /**
     * Ya tiene datos recolectados. Igual que Survey::hasResults() pero
     * acotado a esta actividad puntual: cambiar su parroquia/fechas después
     * de recolectar datos falsearía a qué jornada pertenecen.
     */
    public function hasResults(): bool
    {
        return $this->results()->exists();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
