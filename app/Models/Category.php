<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name','order','survey_id'])]

class Category extends Model
{
    //
    use  HasFactory,SoftDeletes;

    protected static function booted()
    {
        static::deleted(function ($category) {
            // Esto buscará todos los resultados asociados y aplicará softDelete
            $category->results()->delete();
        });

        static::restored(function ($category) {
            // Opcional: Si restauras la respuesta, podrías restaurar los resultados
            $category->results()->restore();
        });
    }
    
    public function surveys(): BelongsTo {

        return $this->belongsTo(Survey::class);

    }

    public function questions(): HasMany {
        
        return $this->hasMany(Question::class);
        
    }

}
