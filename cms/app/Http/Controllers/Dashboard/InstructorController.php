<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Program;
use App\Models\SchoolProgram;
use App\Models\School;
use App\Models\Stage;
use App\Models\TeacherProgram;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use DB;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->hasRole('school')) {
            $query = User::with(['details.stage', 'teacher_programs.program'])
                ->where('role', '1')->where('is_student', '0')->where("school_id", Auth::user()->school_id);
        } else {
            $query = User::with(['details.stage', 'teacher_programs.program'])
                ->where('role', '1')->where('is_student', '0');
        }

        if ($request->filled('school')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('school_id', $request->input('school'));
            });
            $programs = Program::with('course')
                ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                ->join('courses', 'programs.course_id', '=', 'courses.id')
                ->join('stages', 'programs.stage_id', '=', 'stages.id')
                ->where('school_programs.school_id', $request->school)
                ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                ->get();

            $classes = Group::where('school_id', $request->school)
                ->with(['program', 'program.course', 'program.stage'])
                ->get();
        } else {
            $classes = Group::all();
            $programs = Program::all();
        }

        if ($request->filled('program') && $request->program != null) {
            $query->whereHas('teacher_programs', function ($q) use ($request) {
                $q->where('program_id', $request->input('program'));
            });
        }

        if ($request->filled('grade')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('stage_id', $request->input('grade'));
            });
        }

        if ($request->filled('group')) {
            $query->whereHas('groupTeachers', function ($q) use ($request) {
                $q->where('group_id', $request->input('group'));
            });
        }
        $instructors = $query->paginate(10);
        $schools = School::all();
        // $programs = Program::all();
        $grades = Stage::all();
        // $classes = Group::all();

        return view('dashboard.instructors.index', compact('instructors', 'schools', 'programs', 'grades', 'classes'));
    }

    // public function create()
    // {
    //     $user = auth()->user();
    //     $stages = Stage::all();
    //     $groups = Group::all();

    //     if ($user->hasRole('school')) {
    //         $schoolId = $user->school->id;
    //         $programs = SchoolProgram::where('school_id', $schoolId)->get();
    //         $schools = School::where('id', $schoolId)->get();
    //     } else {
    //         $schools = School::all();
    //         $programs = SchoolProgram::all();
    //     }

    //     return view('dashboard.instructors.create', compact('schools', 'programs', 'stages', 'groups'));
    // }

    public function create()
    {
        $user = auth()->user();
        $stages = Stage::all();
        $groups = Group::all();

        if ($user->hasRole('school')) {
            $schoolId = $user->school->id;
            $programs = SchoolProgram::where('school_id', $schoolId)->with('program')->get();
            $schools = School::where('id', $schoolId)->get();
            // Get unique class names with their respective first ID
            $uniqueClasses = Group::where('school_id', $schoolId)
                ->selectRaw('MIN(id) as id, name')
                ->groupBy('name')
                ->get();
        } else {
            $schools = School::all();
            $programs = SchoolProgram::with('program')->get();
            // Get unique class names with their respective first ID for all schools
            $uniqueClasses = Group::selectRaw('MIN(id) as id, name')
                ->groupBy('name')
                ->get();
        }
        return view('dashboard.instructors.create', compact('schools', 'programs', 'stages', 'groups', 'uniqueClasses'));
    }



    public function getGroups($school_id)
    {
        $groups = Group::where('school_id', $school_id)
            ->with(['program', 'program.course', 'program.stage'])
            ->get();

        return response()->json($groups);
    }

    public function getCourses($stageId, $schoolId)
    {
        $courses = SchoolProgram::where('school_id', $schoolId)
            ->whereHas('program', function ($query) use ($stageId) {
                // Filter programs by stage_id in the related Program model
                $query->where('stage_id', $stageId);
            })
            ->with('program.course')
            ->get();

        return response()->json($courses);
    }




    //      public function store(Request $request)
    // {
    // $progs_check =array();
    //         foreach($request->group_id as $grp){

    //             // dd(Program::join('groups','programs.id','groups.program_id')->where('programs.id',Group::find($grp)->program_id)->select('programs.*')->first()->id);
    //             if(!in_array(Group::find($grp)->program_id,$progs_check))
    //             array_push($progs_check,Group::find($grp)->program_id);
    //         }
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'phone' => 'required|string|max:15',
    //         'password' => 'required|string|confirmed|min:6',
    //         'school_id' => 'required|exists:schools,id',
    //         'group_id' => 'nullable|exists:groups,id',
    //         'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    //     ]);

    //     $teacher = User::create([
    //             'name' => $request->name,
    //             'email' => $request->email,
    //             'password' => Hash::make($request->password),
    //             'phone' => $request->phone,
    //             'school_id' => $request->school_id,
    //             'role_type' => $request->role_type,
    //             'role' => '1',
    //             'is_student' => 0
    //         ]);

    //     // UserDetails::create([
    //     //     'user_id' => $user->id,
    //     //     'school_id' => $request->school_id,
    //     //     'stage_id' => null
    //     // ]);
    // UserDetails::create([
    //             'user_id' => $teacher->id,
    //             'school_id' => $request->school_id,
    //             'stage_id' => $request->stage_id
    //         ]);

    //         // if ($request->filled('group_id')) {
    //         //     foreach ($request->group_id as $group_id) {
    //         //         $group = Group::findOrFail($group_id);
    //         //         $group->update(['teacher_id' => $teacher->id]);

    //         //         TeacherProgram::create([
    //         //         'teacher_id' => $teacher->id,
    //         //         'program_id' => Group::find($group_id)->program_id,
    //         //         'grade_id' => Group::find($group_id)->stage_id
    //         //     ]);
    //         //     }
    //         // }

    // if ($request->filled('group_id')) {
    //         $sameProgram = false; 

    //         foreach ($request->group_id as $group_id) {
    //             $group = Group::findOrFail($group_id);
    //             $group->update(['teacher_id' => $teacher->id]);

    //             $teacherProgram = [
    //                 'teacher_id' => $teacher->id,
    //                 'grade_id' => $group->stage_id
    //             ];

    //             if (!$sameProgram) {
    //                 $teacherProgram['program_id'] = $progs_check;
    //                 $sameProgram = true;
    //             }

    //             TeacherProgram::create($teacherProgram);
    //         }
    //     }
    //         $teacher->assignRole('teacher');

    //     // if ($request->has('group_id')) {
    //     //     foreach ($request->group_id as $group_id) {
    //     //         UserCourse::create([
    //     //             'user_id' => $user->id,
    //     //             'program_id' => Group::find($group_id)->program_id
    //     //         ]);
    //     //         GroupStudent::create([
    //     //             'group_id' => $group_id,
    //     //             'student_id' => $user->id
    //     //         ]);
    //     //     }
    //     // }

    //     if ($request->hasFile('parent_image')) {
    //         $imagePath = $request->file('parent_image')->store('images', 'public');
    //         $user->parent_image = $imagePath;
    //         $user->save();
    //     }

    //     return redirect()->route('instructors.index')->with('success', 'Teacher created successfully.');
    // }


    public function store(Request $request)
    {
        $progs_check = array();
        // foreach($request->group_id as $grp){

        //     // dd(Program::join('groups','programs.id','groups.program_id')->where('programs.id',Group::find($grp)->program_id)->select('programs.*')->first()->id);
        //     if(!in_array(Group::find($grp)->program_id,$progs_check))
        //     array_push($progs_check,Group::find($grp)->program_id);
        // }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users',
                'regex:/^[\w.%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/',
                'regex:/\.com$/',
            ],
            'phone' => 'required|string|regex:/^[0-9]+$/|max:15',
            'password' => 'required|string|confirmed|min:6',
            'school_id' => 'required|exists:schools,id',
            'gender_id' => 'required|string|in:boy,girl',
            // 'group_id' => 'nullable|array',
            // 'group_id.*' => 'exists:groups,id',
            'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => (int)$request->phone,
            'school_id' => $request->school_id,
            'gender' => $request->gender_id,
            'role' => '1',
            'is_student' => 0
        ]);

        UserDetails::create([
            'user_id' => $teacher->id,
            'school_id' => $request->school_id,
            'stage_id' => $request->stage_id
        ]);
        //     if ($request->filled('group_id')) {
        //     foreach ($request->group_id as $group_id) {
        //         $group = Group::findOrFail($group_id);
        //         $group->update(['teacher_id' => $teacher->id]);



        //     }

        //     foreach ($progs_check as $program_id) {
        //             TeacherProgram::create([
        //                 'teacher_id' => $teacher->id,
        //                 'program_id' => $program_id,
        //                 'grade_id' => Program::find($program_id)->stage_id
        //             ]);
        //         }
        // }

        // if ($request->filled('group_id')) {
        //     foreach ($request->group_id as $group_id) {
        //         $group = Group::findOrFail($group_id);
        //         $group->update(['teacher_id' => $teacher->id]);

        //         TeacherProgram::create([
        //         'teacher_id' => $teacher->id,
        //         'program_id' =>$progs_check,
        //         'grade_id' => Group::find($group_id)->stage_id
        //     ]);
        //     }
        // }
        if ($request->hasFile('parent_image')) {
            $imagePath = $request->file('parent_image')->store('images', 'public');
            $teacher->update(['parent_image' => $imagePath]);
        }

        return redirect()->route('instructors.index')->with('success', 'Teacher created successfully.');
    }
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'phone' => 'nullable|string|max:15',
    //         'password' => 'required|string|confirmed|min:6',
    //         'school_id' => 'required|exists:schools,id',
    //         // 'program_id' => 'required|array',
    //         // 'program_id.*' => 'exists:programs,id',
    //         // 'stage_id' => 'required|exists:stages,id',
    //         'group_id' => 'nullable|array',
    //         'group_id.*' => 'exists:groups,id',
    //         'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    //     ]);

    //     $teacher = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'phone' => $request->phone,
    //         'school_id' => $request->school_id,
    //         'role' => '1',
    //         'is_student' => 0
    //     ]);

    //     // foreach ($request->program_id as $program_id) {
    //     //     $program = Program::find($program_id);
    //     //     $prog = Program::where('name', 'Mindbuzz')
    //     //         ->where('course_id', $program->course_id)
    //     //         ->where('stage_id', $program->stage_id)
    //     //         ->first();

    //     // }

    //     UserDetails::create([
    //         'user_id' => $teacher->id,
    //         'school_id' => $request->school_id,
    //         'stage_id' => $request->stage_id
    //     ]);

    //     if ($request->filled('group_id')) {
    //         foreach ($request->group_id as $group_id) {
    //             $group = Group::findOrFail($group_id);
    //             $group->update(['teacher_id' => $teacher->id]);

    //             TeacherProgram::create([
    //             'teacher_id' => $teacher->id,
    //             'program_id' => Group::find($group_id)->program_id,
    //             'grade_id' => Group::find($group_id)->stage_id
    //         ]);
    //         }
    //     }

    //     $teacher->assignRole('teacher');

    //     if ($request->hasFile('parent_image')) {
    //         $imagePath = $request->file('parent_image')->store('images', 'public');
    //         $teacher->update(['parent_image' => $imagePath]);
    //     }

    //     return redirect()->route('instructors.index')->with('success', 'Teacher created successfully.');
    // }

    public function edit(string $id)
    {
        $instructor = User::findOrFail($id);
        $user = User::findOrFail($id);
        $stages = Stage::all();
        $groups = Group::all();

        if ($user->hasRole('school')) {
            $schoolId = $user->school->id;
            $programs = Program::where('school_id', $schoolId)->get();
            $schools = School::where('id', $schoolId)->get();
        } else {
            $schools = School::all();
            $programs = Program::all();
        }
        // dd($instructor);

        return view('dashboard.instructors.edit', compact('instructor', 'schools', 'programs', 'stages', 'groups'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:15',
            'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);


        // dd($request->all());
        $instructor = User::findOrFail($id);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            // 'school_id' => $request->school_id,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $instructor->update($updateData);

        // TeacherProgram::where('teacher_id', $instructor->id)->delete();
        // foreach ($request->program_id as $program_id) {
        //     TeacherProgram::create([
        //         'teacher_id' => $instructor->id,
        //         'program_id' => $program_id,
        //         'grade_id' => $request->stage_id
        //     ]);
        // }

        // UserDetails::where('user_id', $instructor->id)->update([
        //     'school_id' => $request->school_id,
        //     'stage_id' => $request->stage_id
        // ]);

        // if ($request->filled('group_id')) {
        //     foreach ($request->group_id as $group_id) {
        //         $group = Group::findOrFail($group_id);
        //         $group->update(['teacher_id' => $instructor->id]);
        //     }
        // }

        if ($request->hasFile('parent_image')) {
            $imagePath = $request->file('parent_image')->store('images', 'public');
            $instructor->update(['parent_image' => $imagePath]);
        }
        $redirectUrl = session('teachers_previous_url', route('instructors.index'));
        return redirect($redirectUrl)->with('success', 'Teacher updated successfully.');
    }

    public function destroy(string $id)
    {
        $instructor = User::findOrFail($id);
        $instructor->delete();
        $redirectUrl = session('teachers_previous_url', route('instructors.index'));

        return redirect($redirectUrl)->with('success', 'Teacher deleted successfully.');
    }

    public function massDestroy(Request $request)
    {
        // dd($request->all());
        $ids = $request->input('ids'); // Accept an array of IDs from the request
        if ($ids) {
            User::whereIn('id', $ids)->delete();
            $redirectUrl = session('teachers_previous_url', route('instructors.index'));
            return redirect($redirectUrl)->with('success', 'Teachers deleted successfully.');
        }
        return redirect()->route('instructors.index')->with('error', 'No Teachers selected.');
    }

    public function getCommonTeacherPrograms(Request $request, $teacherIds)
    {
        $teacherIdsArray = explode(',', $teacherIds);
        $commonProgramIds = null;
        foreach ($teacherIdsArray as $teacherId) {
            $programIds = TeacherProgram::where('teacher_id', $teacherId)->pluck('program_id')->toArray();
            if (is_null($commonProgramIds)) {
                // For the first group, initialize the commonProgramIds
                $commonProgramIds = $programIds;
            } else {
                // Find the intersection of program IDs
                $commonProgramIds = array_intersect($commonProgramIds, $programIds);
            }

            // If no common programs remain, break early
            if (empty($commonProgramIds)) {
                break;
            }
        }
        $programs = Program::whereIn('id', $commonProgramIds)->get();

        // Format the program data for the response
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });

        return response()->json($programsData);
    }

    public function getTeacherPrograms($teacherId)
    {
        $teacherPrograms = TeacherProgram::where('teacher_id', $teacherId)->pluck('program_id');
        $programs = Program::whereIn('id', $teacherPrograms)->get();
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });
        return response()->json($programsData);
    }
}
