<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarmupTest extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function warmup(){
        return $this->belongsTo(Warmup::class,'warmup_id');
    }
    public function test(){
        return $this->hasMany(Test::class,'test_id');
    }

    // public function test(){
    //     return $this->belongsTo(Test::class);
    // }

}
