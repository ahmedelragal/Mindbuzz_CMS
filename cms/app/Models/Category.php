<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function ebooks()
    {
        return $this->hasMany(Ebook::class);
    }

    public function lessonPlans()
    {
        return $this->hasMany(LessonPlan::class);
    }

    public function ppts()
    {
        return $this->hasMany(PPT::class);
    }
}
