<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProgram extends Model
{
    use HasFactory;
    protected $guarded = [];
 
 public function program()
    {
        return $this->belongsTo(Program::class , 'program_id');
    }
    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
