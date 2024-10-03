<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class , 'user_id');
    }

    public function program(){
        return $this->belongsTo(Program::class , 'program_id');
    }
    public function course(){
        return $this->belongsTo(Course::class , 'course_id');
    }

    public function  getImageAttribute($val)
    {
        return ($val !== null) ? storage_path('uploads/' . $val) : "";
    }
}
