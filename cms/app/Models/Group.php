<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
    public function group_students()
    {
        return $this->hasMany(GroupStudent::class);
    }


    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('/storage/' . $val) : "";
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
    public function groupCourses()
    {
        return $this->hasMany(GroupCourse::class, 'group_id', 'id');
    }
    
public function groupStudents()
{
    return $this->hasMany(GroupStudent::class, 'group_id');
}
public function groupTeachers()
{
    return $this->belongsToMany(User::class, 'group_teachers', 'group_id', 'teacher_id');
}
public function groupcoTeachers()
{
    return $this->belongsToMany(User::class, 'group_teachers', 'group_id','co_teacher_id');
}
public function groupTeacher(){
    return $this->hasMany(GroupTeachers::class, 'group_id');
    
}

public function groupTeach()
{
    return $this->belongsToMany(User::class, 'group_teachers', 'group_id', 'teacher_id');
}

}
