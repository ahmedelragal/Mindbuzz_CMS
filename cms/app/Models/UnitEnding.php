<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitEnding extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function unit(){
        return $this->belongsTo(Unit::class , 'unit_id');
    }

    public function test(){
        return $this->belongsTo(Test::class , 'test_id');
    }
    public function warmup(){
        return $this->belongsTo(Warmup::class , 'warmup_id');
    }
    public function bank(){
        return $this->belongsTo(QuestionBank::class , 'bank_id');
    }


}
