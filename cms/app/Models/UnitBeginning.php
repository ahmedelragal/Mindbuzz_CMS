<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitBeginning extends Model
{
    use HasFactory;
    protected $fillable = [
        'unit_id',
        'test_id',
        'video',
        'video_author',
        'video_message',
        'doc',
        'test',
      
    ];
    public function unit(){
        return $this->belongsTo(Unit::class , 'unit_id');
    }

    public function test(){
        return $this->belongsTo(Test::class , 'test_id');
    }
    public function  getTestAttribute($val)
    {
        return ($val !== null) ? asset('/uploads/assignments/' . $val) : "";
    }
    public function  getDocAttribute($val)
    {
        return ($val !== null) ? asset('/uploads/documents/' . $val) : "";
    }

}
