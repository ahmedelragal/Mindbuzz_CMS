<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stage extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class,'id');
    }
    public function school(): BelongsTo
    {
        return $this->BelongsTo(School::class,'id');
    }
    public function course(): BelongsTo
    {
        return $this->BelongsTo(Course::class,'id');
    }

}
