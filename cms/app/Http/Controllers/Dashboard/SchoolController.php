<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Log;
use Spatie\Permission\Models\Role;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $schools = DB::table('users')->where('is_active', 1)
        //     ->join('schools', 'users.school_id', '=', 'schools.id')
        //     ->select('schools.id', 'schools.name', 'users.email', 'users.phone', 'schools.type', 'users.id as user_id')
        //     ->where('users.role', 3)
        //     ->simplePaginate(10);
        $schools = User::where('is_active', 1)
            ->role('school') // Apply role filter using Spatie’s role management
            ->join('schools', 'users.school_id', '=', 'schools.id')
            ->select('schools.id', 'schools.name', 'users.email', 'users.phone', 'schools.type', 'users.id as user_id')
            ->paginate(10);


        return view('dashboard.school.index', compact('schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.school.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|email|unique:users',
            'phone' => 'required|numeric',
            'type' => 'required|string|in:national,international',
            'password' => 'required|string|min:6|confirmed',
        ]);

        DB::beginTransaction();

        try {
            $school = School::create([
                'name' => $request->name,
                'type' => $request->type,
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 3,
                'is_active' => 1,
                'phone' => $request->phone,
                'school_id' => $school->id,
            ]);

            $user->assignRole('school');

            DB::commit();

            return redirect()->route('schools.index')->with('success', 'School created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error creating school or user: ' . $e->getMessage());
            return redirect()->route('schools.create')->with('error', 'There was an error creating the school or user. Please try again.');
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $roles = Role::pluck('name', 'name')->all();

        $school = School::findOrFail($id);
        $schools = User::where('school_id', $id)->firstOrFail();
        $currentRole = $schools->role;

        return view("dashboard.school.edit", compact("schools", 'roles', 'school', 'currentRole'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|numeric',
            'type' => 'required|string|in:national,international',
            'password' => 'nullable|string|min:6',
        ]);

        $school = School::findOrFail($id);
        $user = User::where('school_id', $id)->firstOrFail();

        DB::beginTransaction();

        try {
            $school->update([
                'name' => $request->name,
                'type' => $request->type,
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            DB::commit();

            return redirect()->route('schools.index', $id)->with('success', 'School updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error updating school or user: ' . $e->getMessage());

            return redirect()->route('schools.edit', $id)->with('error', 'There was an error updating the school or user. Please try again.');
        }
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $school = School::findOrFail($id);

        // Delete image if exists
        if ($school->image) {
            Storage::disk('public')->delete($school->image);
        }

        $school->delete();

        return redirect()->route('schools.index')->with('success', 'School deleted successfully!');
    }
    public function getStudentsSchool($schoolId)
    {
        $students =  User::where('school_id', $schoolId)->where('role', 2)->where('is_student', 1)->get();

        // $class =  Group::findOrFail(GroupStudent::where('student_id', $students->id)->pluck('group_id'))->first()->name;
        return response()->json($students);
    }
    public function getTeachersSchool($schoolId)
    {
        $teachers =  User::where('school_id', $schoolId)->where('role', 1)->get();

        return response()->json($teachers);
    }
}
