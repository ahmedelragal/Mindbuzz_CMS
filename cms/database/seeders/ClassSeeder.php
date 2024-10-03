<?php

namespace Database\Seeders;

use App\Models\Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Group::create([
            'name' => 'Class 1',
            'school_id' => 1, 
        ]);

        Group::create([
            'name' => 'Class 2',
            'school_id' => 1,
        ]);

        Group::create([
            'name' => 'Class 3',
            'school_id' => 2, 
        ]);
    }
}
