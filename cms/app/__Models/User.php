<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function program()
    {
        return $this->hasMany(Program::class, 'program_id');
    }
    public function groups()
    {
        return $this->hasMany(GroupStudent::class, 'student_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function details()
    {
        return $this->hasMany(UserDetail::class, 'user_id');
    }
    public function teacher_programs()
    {
        return $this->hasMany(TeacherProgram::class, 'teacher_id');
    }
    public function userCourses()
    {
        return $this->hasMany(UserCourse::class);
    }
    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('uploads/users/' . $val) : "";
    }
    public function getParentImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/images/'. basename($val)) : "";
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
