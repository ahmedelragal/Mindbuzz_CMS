<?php

namespace App\Models;

use App\Enums\UnitName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'number',
        'warmup_id',
        'unit_id',
    ];
    public function warmup()
    {
        return $this->belongsTo(Warmup::class, 'warmup_id');
    }

    public function schoolLesson()
    {
        return $this->hasMany(SchoolLesson::class, 'lesson_id', 'id');
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
    public function game(): HasMany
    {
        return $this->hasMany(Game::class);
    }
        public function game_type(): HasMany
    {
        return $this->hasMany(GameType::class);
    }
    public function tests(): HasMany
    {
        return $this->hasMany(Question::class);
    }
    public function presentation(): HasMany
    {
        return $this->hasMany(Presentation::class);
    }


}
