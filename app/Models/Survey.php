<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name','init_date','finish_date'])]

class Survey extends Model
{
    //
    use SoftDeletes;

    public function categories(): HasMany {

        return $this->hasMany(Category::class);

    }
}
