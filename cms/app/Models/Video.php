<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
    public function hasFile()
    {
        return !is_null($this->file_path);
    }
    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : "";
    }
    public function getFilePathAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : null;
    }
}
