<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSkills extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function game_type()
    {
        return $this->belongsTo(GameType::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skills::class);
    }
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
