<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CookingStep extends Model
{
    protected $table = 'cooking_steps';

    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'step_number',
        'description',
        'image',
    ];

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class, 'recipe_id');
    }
}
