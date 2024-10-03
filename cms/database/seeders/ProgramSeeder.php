<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Program::create([
            'name' => 'Program 1',
            'school_id' => 1, // Assuming a school with ID 1 exists
            'course_id' => 1, // Assuming a course with ID 1 exists
            'stage_id' => 1,  // Assuming a stage with ID 1 exists
        ]);

        Program::create([
            'name' => 'Program 2',
            'school_id' => 2, // Assuming a school with ID 2 exists
            'course_id' => 2, // Assuming a course with ID 2 exists
            'stage_id' => 2,  // Assuming a stage with ID 2 exists
        ]);


    }
}
