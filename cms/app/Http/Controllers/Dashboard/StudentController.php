<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Imports\StudentsImport;
use App\Imports\UsersImport;
use App\Models\Group;
use App\Models\GroupStudent;
use App\Models\Program;
use App\Models\School;
use App\Models\SchoolProgram;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->hasRole('school')) {
            $query = User::with(['details.stage', 'userCourses.program', 'groups'])
                ->where('role', '2')
                ->where('is_student', 1)
                ->where('school_id', Auth::user()->school_id);
        } else {
            $query = User::with(['details.stage', 'userCourses.program', 'groups'])
                ->where('role', '2')
                ->where('is_student', 1);
        }


        if ($request->filled('school')) {
            $query->where('school_id', $request->input('school'));
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
            $programs = Program::with('course')->when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
            $classes = Group::when(Auth::user()->hasRole('school'), function ($query) {
                return $query->where('school_id', Auth::user()->school_id);
            })->get();
        }

        if ($request->filled('program')) {
            $query->whereHas('userCourses', function ($q) use ($request) {
                $q->where('program_id', $request->input('program'));
            });
        }

        if ($request->filled('grade')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('stage_id', $request->input('grade'));
            });
        }

        if ($request->filled('group')) {
            $query->whereHas('groups', function ($q) use ($request) {
                $q->where('group_id', $request->input('group'));
            });
        }
        if ($request->filled('school'))
            $students = $query->paginate(10);
        else
            $students = $query->paginate(10);

        $schools = School::all();

        $grades = Stage::all();


        return view('dashboard.students.index', compact('students', 'schools', 'programs', 'grades', 'classes'));
    }
    public function getGroups($school_id)
    {
        $groups = Group::where('school_id', $school_id)
            ->selectRaw('MIN(id) as id, name')
            ->groupBy('name')
            ->get();


        return response()->json($groups);
    }

    public function getDuplicateClasses($classId, $schoolId)
    {
        // Assuming the class ID is provided, find the class name and then get all classes with that name
        $class = Group::find($classId);
        $duplicateClasses = Group::where('name', $class->name)->with(['program.course', 'program.stage'])->where('school_id', $schoolId)->get();

        // dd($duplicateClasses,$class,$schoolId);
        return response()->json($duplicateClasses);
    }

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

        return view('dashboard.students.create', compact('schools', 'programs', 'stages', 'groups', 'uniqueClasses'));
    }



    public function store(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|confirmed|min:6',
            'school_id' => 'required|exists:schools,id',
            'gender_id' => 'required|string|in:boy,girl',
            'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'school_id' => $request->school_id,
            'gender' => $request->gender_id,
            'role' => '2',
            'is_student' => 1
        ]);
        if ($user) {
            UserDetails::create([
                'user_id' => $user->id,
                'school_id' => $request->school_id,
                'stage_id' => null
            ]);
        } else {
            return redirect()->route('students.create')->with('success', 'Student not created.');
        }



        // if ($request->has('group_id')) {
        //     foreach ($request->group_id as $group_id) {
        //         UserCourse::create([
        //             'user_id' => $user->id,
        //             'program_id' => Group::find($group_id)->program_id
        //         ]);
        //         GroupStudent::create([
        //             'group_id' => $group_id,
        //             'student_id' => $user->id
        //         ]);
        //     }
        // }

        if ($request->hasFile('parent_image')) {
            $imagePath = $request->file('parent_image')->store('images', 'public');
            $user->parent_image = $imagePath;
            $user->save();
        }

        return redirect()->route('students.index')->with('success', 'Student created successfully.');
    }


    public function import(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $schoolId = $request->input('school_id');

        // Load and process the Excel file
        Excel::import(new StudentsImport($schoolId), $request->file('file'));

        return redirect()->back()->with('success', 'Students imported successfully.');
    }

    public function show(string $id) {}

    public function edit(string $id)
    {
        $student = User::findOrFail($id);
        $schoolId = $student->school_id;
        $schools = School::all();
        $userDetails = UserDetails::where('user_id', $id)->first();

        if ($userDetails && $userDetails->stage_id) {
            $programs = Program::where('stage_id', $userDetails->stage_id)->get();
        } else {
            $programs = Program::all();
        }

        $stages = Stage::all();

        // Fetch all groups with their relationships (program, course, stage)
        $groups = Group::with(['program.course', 'stage'])
            ->where('school_id', $schoolId)
            ->get();

        // Group the fetched data by 'name' in PHP to remove duplicates by name
        $groupedGroups = $groups->groupBy('name')->map(function ($groupList) {
            return $groupList->first();  // Get only the first group for each unique name
        });

        // Get the pre-selected groups for this student
        $selectedGroups = GroupStudent::where('student_id', $id)->pluck('group_id')->toArray();

        return view('dashboard.students.edit', compact('student', 'schools', 'programs', 'stages', 'groupedGroups', 'selectedGroups'));
    }


    public function update(Request $request, string $id)
    {

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|string|max:15',
            'password' => 'required|string|confirmed|min:6',
            'parent_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Fetch the student to be updated
        $student = User::findOrFail($id);

        // Update basic user information
        $student->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'school_id' => $request->school_id
        ]);

        // Handle password update if provided
        if ($request->filled('password')) {
            $student->update([
                'password' => Hash::make($request->password)
            ]);
        }

        // Update user details (such as school and stage)
        UserDetails::where('user_id', $student->id)->update([
            'school_id' => $request->school_id,
            'stage_id' => $request->stage_id
        ]);



        // Handle image upload if provided
        if ($request->hasFile('parent_image')) {
            $imagePath = $request->file('parent_image')->store('images', 'public');
            $student->update([
                'parent_image' => $imagePath
            ]);
        }

        // Redirect back to the student index with a success message
        return redirect()->route('students.index')->with('success', 'Student updated successfully.');
    }



    public function destroy(string $id)
    {
        $student = User::findOrFail($id);
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Student deleted successfully.');
    }

    public function getCourses($stageId, $schoolId)
    {
        $courses = SchoolProgram::where('school_id', $schoolId)
            ->whereHas('program', function ($query) use ($stageId) {
                // Filter programs by stage_id in the related Program model
                $query->where('stage_id', $stageId);
            })
            ->with('program.course') // Load the related Program data
            ->get();


        return response()->json($courses);
    }

    public function getCommonStudentPrograms($studentId1, $stundentId2)
    {

        $studentPrograms1 = UserCourse::where('user_id', $studentId1)->pluck('program_id')->toArray();
        $studentPrograms2 = UserCourse::where('user_id', $stundentId2)->pluck('program_id')->toArray();

        $commonProgramIds = array_intersect($studentPrograms1, $studentPrograms2);
        $programs = Program::whereIn('id', $commonProgramIds)->get();
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });
        return response()->json($programsData);
    }
    public function getStudentPrograms($studentId)
    {
        $studentPrograms = UserCourse::where('user_id', $studentId)->pluck('program_id');
        $programs = Program::whereIn('id', $studentPrograms)->get();
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });
        return response()->json($programsData);
    }
}
