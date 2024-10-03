<?php

namespace App\Imports;

use App\Models\User;
use App\Models\School;
use App\Models\UserDetails;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsFailures;
    protected $schoolId;
    protected $schoolName;

    public function __construct($schoolId)
    {
        $this->schoolId = $schoolId;
        $this->schoolName = Str::slug(School::findOrFail($schoolId)->name);
    }

    public function model(array $row)
    {
        $firstName = Str::slug($row['name'], '');
        $email = $this->generateUniqueEmail($firstName);

        $user = User::create([
            'name' => $row['name'],
            'gender' => $row['gender'],
            'email' => $email,
            'phone' => $row['phone'],
            'password' => Hash::make('password123'),
            'role' => 2,
            'school_id' => $this->schoolId,
            'is_student' => 1,
            'is_active' => 1,
        ]);

        // Adding the user to the user_details table
        UserDetails::create([
            'user_id' => $user->id,
            'school_id' => $this->schoolId,
            'stage_id' => null
        ]);
        return $user;
    }

    protected function generateUniqueEmail($firstName)
    {
        $baseEmail = "{$firstName}@{$this->schoolName}.com";
        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$firstName}{$counter}@{$this->schoolName}.com";
            $counter++;
        }

        return $email;
    }

    public function rules(): array
    {
        return [
            '*.name' => 'required|string',
            '*.gender' => 'required|string|in:boy,girl',
            '*.phone' => 'required|string',
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        // Handle the exception as you'd like, e.g., log the error.
    }
}
