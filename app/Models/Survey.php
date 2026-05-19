<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name','init_date','finish_date'])]

class Survey extends Model
{
    //

    public function categories(): HasMany {

        return $this->hasMany(Category::class);

    }
}
