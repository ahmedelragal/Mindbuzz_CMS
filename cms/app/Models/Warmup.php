<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warmup extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function warmup_video(){
        return $this->hasMany(WarmupVideo::class ,'id', 'warmup_id');
    }
    public function warmup_test(){
        return $this->hasMany(WarmupTest::class ,'id', 'warmup_id');
    }
}
