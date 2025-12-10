<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IngredientCategory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
<<<<<<< HEAD
        'created_at',
    ];

=======
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Category has many master ingredients
     */
>>>>>>> origin/main
    public function masterIngredients(): HasMany
    {
        return $this->hasMany(MasterIngredient::class, 'category_id');
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> origin/main
