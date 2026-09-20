<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'sex_id', 'age', 'parish_id', 'rol_id', 'disabled_at', 'disabled_reason'])]

#[Hidden(['password', 'remember_token'])]
class Person extends Authenticatable
{
    protected $table = 'persons';

    /** @use HasFactory<PersonFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * Send the password reset notification.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'disabled_at' => 'datetime',
        ];
    }

    public function rol(): BelongsTo
    {

        return $this->belongsTo(Rol::class);

    }

    public function sex(): BelongsTo
    {

        return $this->belongsTo(Sex::class);

    }

    public function parish(): BelongsTo
    {

        return $this->belongsTo(Parish::class);

    }

    public function results(): HasMany
    {

        return $this->hasMany(Result::class);

    }

    public function assignedSurveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'survey_person')
            ->using(SurveyPerson::class)
            ->withPivot(['assigned_by', 'assigned_at', 'unassigned_at', 'unassigned_by'])
            ->withTimestamps();
    }

    /**
     * Solo las encuestas con una asignación activa (sin desasignar) a este
     * encuestador — usado por la app móvil para saber qué puede descargar.
     */
    public function activeAssignedSurveys(): BelongsToMany
    {
        return $this->assignedSurveys()->wherePivotNull('unassigned_at');
    }
}
