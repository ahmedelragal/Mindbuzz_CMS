<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BenchmarkAssignTo extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function benchmark()
    {
        return $this->belongsTo(Benchmark::class);
    }
    public function units()
    {
        return $this->belongsTo(Unit::class);
    }
}
