<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentTest extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function tests()
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }



    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('test_image/' . $val) : "";
    }
}
