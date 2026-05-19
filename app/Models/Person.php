<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use LDAP\Result;

#[Fillable(['name', 'email', 'password','sex_id','age_range_id','parish_id'])]

#[Hidden(['password', 'remember_token'])]
class Person extends Authenticatable
{
    protected $table = 'persons';

    /** @use HasFactory<PersonFactory> */
    use HasFactory, Notifiable;

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
        ];
    }

    public function rol(): BelongsTo {

        return $this->belongsTo(Rol::class);

    }

    public function sex(): BelongsTo {

        return $this->belongsTo(Sex::class);

    }

    public function parish(): BelongsTo {

        return $this->belongsTo(Parish::class);

    }

    public function results(): HasMany {

        return $this->hasMany(Result::class);
        
    }
}
