<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Benchmark extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
}
