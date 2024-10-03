<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    private $permissions = [
        // 'role-list',
        // 'role-create',
        // 'role-edit',
        // 'role-delete',
        // 'class-list',
        // 'class-create',
        // 'class-edit',
        // 'class-delete',
        // 'program-list',
        // 'program-create',
        // 'program-edit',
        // 'program-delete',
        // 'course-list',
        // 'course-create',
        // 'course-edit',
        // 'course-delete',
        // 'stage-list',
        // 'stage-create',
        // 'stage-edit',
        // 'stage-delete',
        // 'school-list',
        // 'school-create',
        // 'school-edit',
        // 'school-delete',
        // 'student-list',
        // 'student-create',
        // 'student-edit',
        // 'student-delete',
        // 'instructor-list',
        // 'instructor-create',
        // 'instructor-edit',
        // 'instructor-delete',
    ];
    public function run(): void
    {
        // $this->call([
        //     SchoolSeeder::class,
        //     ClassSeeder::class,
        //     CourseSeeder::class,
        //     StageSeeder::class,
        //     ProgramSeeder::class,
        // ]);
        foreach ($this->permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create admin User and assign the role to him.
        $user = User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('123456'),
            'phone' => "123",
            'role' => 3,
        ]);

        $role = Role::create();

        $permissions = Permission::pluck('id', 'id')->all();

        $role->syncPermissions($permissions);

        $user->assignRole([$role->id]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}