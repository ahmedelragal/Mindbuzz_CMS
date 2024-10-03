<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Test extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'layout' => 'json',
        // 'type' => TestType::class,
    ];
    public function studentTests(): HasMany
    {
        return $this->HasMany(StudentTest::class, 'test_id');
    }
    public function questions(): HasMany
    {
        return $this->HasMany(Question::class);
    }
    public function warmup(): BelongsTo
    {
        return $this->belongsTo(Warmup::class);
    }
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
    public function user():HasMany
    {
        return $this->hasMany(User::class);
    }
    // public function type(): BelongsTo
    // {
    //     return $this->belongsTo(TestType::class);
    // }


    public function getImageAttribute($val)
    {
        return ($val !== null) ? asset('storage/' . $val) : "";
    }
}
