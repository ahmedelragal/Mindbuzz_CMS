<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
      
    ];
    public function program(): HasMany
    {
        return $this->HasMany(Program::class);
    }
            public function schoolProgram(): belongsTo
    {
        return $this->belongsTo(SchoolProgram::class,'id');
    }
}
