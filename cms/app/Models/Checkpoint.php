<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;
    public function test()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }

    public function bank()
    {
        return $this->belongsTo(QuestionBank::class, 'bank_id');
    }
    public function checkpointAssignedTo()
    {
        return $this->hasMany(CheckpointAssignedTo::class, );
    }

}
