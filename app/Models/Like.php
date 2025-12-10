<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD

class Like extends Model
{
=======
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Like extends Model
{
    public $timestamps = false;

>>>>>>> origin/main
    protected $fillable = [
        'user_id',
        'recipe_id',
    ];

<<<<<<< HEAD
    public $timestamps = false; 
    // Kalau tabel kamu punya created_at saja tanpa updated_at
    // atau kamu handle timestamps manual.
    
    protected $table = 'likes';

    public function user()
=======
    protected $casts = [
        'created_at' => 'datetime',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    /**
     * Like belongs to a user
     */
    public function user(): BelongsTo
>>>>>>> origin/main
    {
        return $this->belongsTo(User::class);
    }

<<<<<<< HEAD
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
=======
    /**
     * Like belongs to a recipe
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }
}
>>>>>>> origin/main
