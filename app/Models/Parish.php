<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Parish extends Model
{
    //

    public function persons(): HasMany {

        return $this->hasMany(Person::class);

    }
}
