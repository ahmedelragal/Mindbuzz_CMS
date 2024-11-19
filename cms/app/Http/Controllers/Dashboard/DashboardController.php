<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Program;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{

    public function index()
    {
        if (Auth::user()->hasRole('school')) {
            $schoolId = Auth::user()->school_id;
            $studentsInSchool = User::where('school_id', $schoolId)
                ->where('role', 2)
                ->where('is_student', 1)
                ->count();
            $teachersInSchool = User::where('school_id', $schoolId)
                ->where('role', 1)
                ->where('is_student', 0)
                ->count();
            $totalSchools = null;
            $nationalSchools = null;
            $internationalSchools = null;
        } else {
            $studentsInSchool = User::where('role', 2)
                ->where('is_student', 1)
                ->count();
            $teachersInSchool = User::where('role', 1)
                ->where('is_student', 0)
                ->count();
            // $totalSchools = DB::table('users')->where('is_active', 1)
            //     ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'national')->orWhere('type', 'international')
            //     ->role('school')->count();
            // $nationalSchools = DB::table('users')->role('school')
            //     ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'national')->count();
            // $internationalSchools = DB::table('users')->role('school')
            //     ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'international')->count();

            $totalSchools = User::where('is_active', 1)
                ->whereHas('school', function ($query) {
                    $query->whereIn('type', ['national', 'international']);
                })->role('school')->count();

            $nationalSchools = User::whereHas('school', function ($query) {
                $query->where('type', 'national');
            })->role('school')->count();

            $internationalSchools = User::whereHas('school', function ($query) {
                $query->where('type', 'international');
            })->role('school')->count();
        }
        $totalUsers = $studentsInSchool + $teachersInSchool;
        if (auth()->user()->hasRole('school')) {
            $totalPrograms = Program::with(['course', 'stage'])
                ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })
                ->count();

            $totalClasses = Group::where('school_id', auth()->user()->school_id)->count();
        } else {
            $totalPrograms = Program::all()->count();
            $totalClasses = Group::all()->count();
        }
        return view(
            'dashboard.index',
            compact(
                'studentsInSchool',
                'teachersInSchool',
                'totalUsers',
                'totalSchools',
                'nationalSchools',
                'internationalSchools',
                'totalPrograms',
                'totalClasses'
            )
        );
    }
    // public function index()
    // {
    //     if (false) {
    //         $schoolId = Auth::user()->school_id;

    //         $studentsInSchool = User::where('school_id', $schoolId)
    //             ->where('role', 2)
    //             ->where('is_student', 1)
    //             ->count();

    //         $teachersInSchool = User::where('school_id', $schoolId)
    //             ->where('role', 1)
    //             ->where('is_student', 0)
    //             ->count();
    //         $totalSchools = null;
    //         $nationalSchools = null;
    //         $internationalSchools = null;
    //     } else {
    //         $studentsInSchool = User::where('role', 2)
    //             ->where('is_student', 1)
    //             ->count();

    //         $teachersInSchool = User::where('role', 1)
    //             ->where('is_student', 0)
    //             ->count();
    //         $totalSchools = DB::table('users')->where('is_active', 1)
    //             ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'national')->orWhere('type', 'international')

    //             ->where('users.role', 3)->count();
    //         $nationalSchools = DB::table('users')->where('role', 3)
    //             ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'national')->count();
    //         $internationalSchools = DB::table('users')->where('role', 3)
    //             ->join('schools', 'users.school_id', '=', 'schools.id')->where('type', 'international')->count();
    //     }

    //     $totalUsers = $studentsInSchool + $teachersInSchool;



    //     return view(
    //         'dashboard.index',
    //         compact(
    //             'studentsInSchool',
    //             'teachersInSchool',
    //             'totalUsers',
    //             'totalSchools',
    //             'nationalSchools',
    //             'internationalSchools'
    //         )
    //     );
    // }
}
