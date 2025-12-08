<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'master_ingredient_id',
        'name',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Ingredient belongs to a recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Ingredient may belong to a master ingredient
     */
    public function masterIngredient(): BelongsTo
    {
        return $this->belongsTo(MasterIngredient::class);
    }
}