<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
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
    public function groupTeachers()
    {
        return $this->hasMany(GroupTeachers::class, 'teacher_id');
    }
    public function teacher()
    {
        return $this->belongsTo(User::class, 'id');
    }

    /**
     * Get the co-teacher associated with the GroupTeacher.
     */
    public function coTeacher()
    {
        return $this->belongsTo(User::class, 'id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }

    // public function role()
    // {
    //     return $this->belongsTo(Role::class);
    // }

    public function details()
    {
        return $this->hasMany(UserDetails::class, 'user_id');
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
        return ($val !== null) ? asset('storage/images/' . basename($val)) : "";
    }
    public function canAccessPanel(Panel $panel): bool
    {
        // dd(in_array('super_admin',$this->roles->pluck('name')->toArray()));

        if ($panel->getId() === 'admin') {
            if (
                in_array('super-admin', $this->roles->pluck('name')->toArray()) ||
                in_array('admin', $this->roles->pluck('name')->toArray())
            ) {
                return true;
            } else {
                return false;
            }
        } elseif ($panel->getId() === 'teacher') {
            if (
                in_array('super_admin', $this->roles->pluck('name')->toArray()) ||
                in_array('admin', $this->roles->pluck('name')->toArray())
                ||
                in_array('Teacher', $this->roles->pluck('name')->toArray())
            )
                return true;
        }
        return false;
    }
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
