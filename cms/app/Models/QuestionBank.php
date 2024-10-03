<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function test()
    {
        return $this->hasMany(Question::class, 'test_id');
    }
    public function questions()
    {
        return $this->hasMany(RevisionQuestionsBank::class,'bank_id');
    }
}
