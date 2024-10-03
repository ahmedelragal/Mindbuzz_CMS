<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function tests()
    {
        return $this->hasMany(Test::class, 'program_id');
    }
    public function test()
    {
        return $this->hasMany(Test::class, 'program_id');
    }
    public function student_tests()
    {
        return $this->hasMany(StudentTest::class, 'program_id');
    }
    public function school()
    {
        return $this->belongsTo(School::class, 'school_id');
    }
    public function stage()
    {
        return $this->belongsTo(Stage::class, 'stage_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function units()
    {
        return $this->hasMany(Unit::class);
    }
    public function beginning()
    {
        return $this->hasOne(Beginning::class);
    }
    public function benchmark()
    {
        return $this->hasMany(Benchmark::class);
    }
    public function ending()
    {
        return $this->hasOne(Ending::class);
    }
    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : "";
    }
}
