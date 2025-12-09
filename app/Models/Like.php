<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'user_id',
        'recipe_id',
    ];

    public $timestamps = false; 
    // Kalau tabel kamu punya created_at saja tanpa updated_at
    // atau kamu handle timestamps manual.
    
    protected $table = 'likes';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
