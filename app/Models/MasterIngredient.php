<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MasterIngredient extends Model
{
    public $timestamps = false; // sesuai ERD
=======
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterIngredient extends Model
{
    public $timestamps = false;
>>>>>>> origin/main

    protected $fillable = [
        'category_id',
        'name',
        'slug',
<<<<<<< HEAD
        'created_at',
    ];

    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_ingredient_tags',
            'master_ingredient_id',
            'recipe_id'
        );
    }

=======
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Master ingredient belongs to a category
     */
>>>>>>> origin/main
    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id');
    }
<<<<<<< HEAD
}
=======

    /**
     * Master ingredient has many ingredients
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }
}
>>>>>>> origin/main
