<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarmupVideo extends Model
{
    use HasFactory;

    public function warmup(){
        return $this->belongsTo(Warmup::class , 'warmup_id');
    }



}
