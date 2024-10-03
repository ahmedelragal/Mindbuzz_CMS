<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestQuestion extends Model
{
    use HasFactory;
    protected $guarded =[];
    
    public function test()
    {
        return $this->belongsTo(Test::class,'test_id');
    }
    public function game()
    {
        return $this->belongsTo(Game::class,'game_id');
    }
}
