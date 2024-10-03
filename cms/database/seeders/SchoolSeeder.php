<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        School::create([
            'name' => ' School 1',
            'email' => 'school@school.com',
            'phone' => '1234567890',
            'type' => 'national',
            'status' => '1',
            'description' => 'A national school.',
            'password' => Hash::make('123456'),
            'image' => 'test.jpg',
        ]);

        School::create([
            'name' => 'School 2',
            'email' => 'school2@school.com',
            'phone' => '0123456789',
            'type' => 'international',
            'status' => '1',
            'description' => 'An international school.',
            'password' => Hash::make('123456'),
            'image' => 'test.jpg',
        ]);
    }
}
