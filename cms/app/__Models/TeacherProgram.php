<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherProgram extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo(Program::class,'program_id');
    }
    public function stage()
    {
        return $this->belongsTo(Stage::class,'grade_id');
    }
        public function getProgramNameAttribute()
    {
        return $this->program->course->name . ' ' . $this->stage->name;
    }
}
