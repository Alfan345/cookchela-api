<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterIngredient extends Model
{
    protected $table = 'master_ingredients';

    // Sesuai ERD: tidak pakai updated_at / otomatis timestamps
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Master ingredient belongs to a category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'category_id');
    }

    /**
     * Master ingredient has many ingredient rows
     * (tabel ingredients, kolom foreign key: master_ingredient_id)
     */
    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'master_ingredient_id');
    }

    /**
     * Master ingredient dipakai di banyak resep
     * lewat pivot recipe_ingredient_tags
     */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(
            Recipe::class,
            'recipe_ingredient_tags',
            'master_ingredient_id',
            'recipe_id'
        );
    }
}
