<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stage::create([
            'name' => 'Stage 1',
        ]);

        Stage::create([
            'name' => 'Stage 2',
        ]);

        Stage::create([
            'name' => 'Stage 3',
        ]);
    }
}
