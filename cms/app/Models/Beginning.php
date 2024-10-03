<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beginning extends Model
{
    use HasFactory;
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
