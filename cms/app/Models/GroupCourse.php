<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupCourse extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id', 'id');
    }
    public function groupStudents(): BelongsTo
    {
        return $this->belongsTo(GroupStudent::class, 'group_id', 'group_id');
    }
}
