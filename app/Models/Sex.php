<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['abbreviation','description'])]
class Sex extends Model
{
    //
    public function persons(): HasMany {

        return $this->hasMany(Person::class);

    }
}
