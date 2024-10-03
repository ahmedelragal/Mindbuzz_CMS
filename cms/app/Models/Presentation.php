<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presentation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function lesson()
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }


    // Check if the file name is provided in the URL

}
