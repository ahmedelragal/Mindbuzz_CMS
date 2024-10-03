<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckpointAssignedTo extends Model
{
    use HasFactory;
    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class);
    }
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

}
