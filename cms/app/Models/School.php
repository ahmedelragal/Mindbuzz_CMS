<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    protected $guarded = [];
// public function course()
//     {
//         return $this->belongsTo(Course::class, 'warmup_id');
//     }
//     public function lesson()
//     {
//         return $this->belongsTo(Warmup::class, 'warmup_id');
//     }
    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
