<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ingredient extends Model
{
    protected $table = 'ingredients';

    public $timestamps = false; // sesuai ERD: only created_at? sesuaikan kalau tabelmu punya timestamps

    protected $fillable = [
        'recipe_id',
        'master_ingredient_id',
        'name',
        'quantity',
        'unit',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
