<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name'])]
class Rol extends Model
{
    /*
    * get persons by roles
    *
    */
    protected $table = 'roles';

    use HasFactory, SoftDeletes;

    public const POLLSTER = 'POLLSTER';

    public const RESPONDENT = 'RESPONDENT';

    public const ADMIN = 'ADMIN';

    public const GESTOR_ENCUESTAS = 'GESTOR_ENCUESTAS';

    public function persons(): HasMany
    {

        return $this->hasMany(Person::class);

    }
}
