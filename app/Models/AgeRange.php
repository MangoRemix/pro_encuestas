<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['init_range','finish_range','range'])]
class AgeRange extends Model
{
    //
    use SoftDeletes;
}
