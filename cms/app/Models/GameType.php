<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameType extends Model
{
    use HasFactory;
    protected $guarded = [];
        public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
        public function skills()
    {
        return $this->hasMany(GameSkills::class);
    }

}
