<?php

namespace App\Models;

use App\Enums\GameFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'layout' => 'json',
        // 'audio_flag' => GameFlag::class,
    ];

    public function prevGame()
    {
        return $this->belongsTo(Game::class, 'prev_game_id');
    }
    public function nextGame()
    {
        return $this->belongsTo(Game::class, 'next_game_id');
    }
    public function gameImages()
    {
        return $this->hasMany(GameImage::class, 'game_id');
    }
    public function gameLetters()
    {
        return $this->hasMany(GameLetter::class, 'game_id');
    }
    public function gameChoices()
    {
        return $this->hasMany(Choice::class, 'game_id');
    }
    public function gameQuestions()
    {
        return $this->hasMany(TestQuestion::class, 'game_id');
    }
    public function gameLettersDistinct()
    {
        return $this->hasMany(GameLetter::class, 'game_id');
    }
    public function gameTypes()
    {
        return $this->belongsTo(GameType::class, 'game_type_id');
    }
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
    public function studentDegrees()
    {
        return $this->hasMany(StudentDegree::class,'game_id');
    }
    
    public function getVideoAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : "";
    }

}
