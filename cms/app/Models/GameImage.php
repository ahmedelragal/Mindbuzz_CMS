<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameImage extends Model
{
    use HasFactory;

    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/img/' . basename($val)) : "";

    }

    protected $guarded = [];
    public function gameLetter()
    {
        return $this->belongsTo(GameLetter::class, "game_letter_id");
    }

}
