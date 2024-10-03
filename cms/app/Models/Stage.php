<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
      
    ];

   
    public function program(): belongsTo
    {
        return $this->belongsTo(Program::class,'id');
    }
        public function schoolProgram(): belongsTo
    {
        return $this->belongsTo(SchoolProgram::class,'id');
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
