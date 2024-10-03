<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;
    public $test = [];
    protected $guarded = [];
    protected $casts = [
        'layout' => 'json',
        // 'type' => QuestionType::class,
        'choices' => 'json',
        'control' => 'json',
        'show_num' => 'boolean',
    ];
    public function choices(): HasMany
    {
        return $this->HasMany(Choice::class, 'question_id');
    }
    public function test():BelongsTo
    {
        return $this->belongsTo(Test::class, 'test_id');
    }
    public function questions()
    {
        return $this->belongsTo(QuestionBank::class);
    }
    public function bank():BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'bank_id');
    }
    public function lesson():BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }
}