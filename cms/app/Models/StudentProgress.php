<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentProgress extends Model
{
    use HasFactory;
    protected $guarded =[];
    
    public function tests()
    {
        return $this->belongsTo(Test::class,'test_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'student_id');
    }
}
