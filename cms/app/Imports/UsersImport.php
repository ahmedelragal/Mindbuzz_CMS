<?php

namespace App\Imports;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\UserCourse;
use App\Models\GroupStudent;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Create the user
        $user = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => Hash::make($row['password']),
            'school_id' => $row['school_id'],
            'is_student' => $row['is_student'],
            'role' => $row['role'],
            'phone' => $row['phone'],
        ]);

        UserCourse::create([
            'user_id' => $user->id,
            'program_id' => $row['program_id'],
        ]);

        UserDetails::create([
            'user_id' => $user->id,
            'school_id' => $row['school_id'],
            'stage_id' => $row['stage_id'],
        ]);

        GroupStudent::create([
            'student_id' => $user->id,
            'group_id' => $row['group_id'],
        ]);

        // Handle parent image if exists
        if (isset($row['parent_image'])) {
            $path = Storage::disk('public')->put('images', $row['parent_image']);
            $user->update(['parent_image' => $path]);
        }

        return $user;
    }
}
