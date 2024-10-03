<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    use HasFactory;
    public function gameSkills()
    {
        return $this->hasMany(GameSkills::class,'skill_id');
    }
}
