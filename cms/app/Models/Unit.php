<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id');
    }
    
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'unit_id');
    }
    public function schoolUnit()
    {
        return $this->hasMany(SchoolUnit::class, 'unit_id', 'id');
    }
     public function  getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : "";
    }

}
