<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class assign_game extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function user()
    {
        return $this->hasMany(User::class);
    }
    public function group()
    {
        return $this->hasMany(Group::class);
    }
    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
