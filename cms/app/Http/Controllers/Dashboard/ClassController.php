<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Group;
use App\Models\Program;
use App\Models\School;
use App\Models\Stage;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\GroupTeachers;
use App\Models\TeacherProgram;
use App\Models\GroupStudent;
use Illuminate\Http\Request;
use DB;
use App\Models\GroupCourse;
use App\Models\SchoolProgram;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Cordinator')) {
            abort(403, 'Unauthorized access.');
        }
        if ($user->hasRole('school')) {
            $schoolId = $user->school->id;
            $schoolClasses = Group::where('school_id', $schoolId)->get()->unique('name');

            $perPage = 10;
            $currentPage = request()->get('page', 1);
            $pagedClasses = new \Illuminate\Pagination\LengthAwarePaginator(
                $schoolClasses->forPage($currentPage, $perPage),
                $schoolClasses->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );
            $schools = School::all();
            return view('dashboard.class.index', compact('pagedClasses', 'schools'));
        }

        // Fetch all classes grouped by school, ensuring only unique names per school

        if ($request->filled('school')) {
            $classes = Group::with('school')->where('school_id', $request->school)->get()->groupBy('school_id');
        } else {
            $classes = Group::with('school')->get()->groupBy('school_id');
        }

        $filteredClasses = collect();
        foreach ($classes as $schoolClasses) {
            $uniqueClasses = $schoolClasses->unique('name'); // Ensure unique class names
            $filteredClasses = $filteredClasses->merge($uniqueClasses);
        }

        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $pagedClasses = new \Illuminate\Pagination\LengthAwarePaginator(
            $filteredClasses->forPage($currentPage, $perPage),
            $filteredClasses->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        $schools = School::all();
        return view('dashboard.class.index', compact('pagedClasses', 'schools'));
    }

    public function view($id)
    {
        // Find the group by ID, along with related models
        $class = Group::with([
            'groupCourses',
            'groupStudents.student',
            'groupTeachers.teacher',
            'groupTeachers.coTeacher',
            'groupTeacher.groupTeach',
            'groupcoTeachers.coTeacher'
        ])->findOrFail($id);

        $user = auth()->user(); // Get logged-in user
        // Check if user has the 'school' role and the class belongs to their school
        if ($user->hasRole('Cordinator')) {
            abort(403, 'Unauthorized access to this class.');
        }
        if ($user->hasRole('school') && $class->school_id !== $user->school_id) {
            abort(403, 'Unauthorized access to this class.');
        }
        $groupedTeachers = $class->groupTeacher->groupBy(function ($groupTeacher) {
            if (!$groupTeacher->teacher)
                return '-';

            return $groupTeacher->teacher->name;
        });

        $teacherNames = $groupedTeachers->keys()->toArray();

        // dd($groupedTeachers);
        $prg = [];
        foreach ($groupedTeachers as $teacherName => $teacherGroup) {
            $prg[$teacherName] = [];
            foreach ($teacherGroup as $groupteacher) {

                $program = Program::find($groupteacher->program_id);

                $prg[$teacherName][] = [
                    'coTeacher' => User::find($groupteacher->co_teacher_id)->name ?? 'No Co-Teacher',
                    'course' => Course::find($program->course_id)->name,
                    'stage' => Stage::find($program->stage_id)->name,
                    'id' => $groupteacher->id
                ];
            }
        }
        // dd($prg);

        // Fetch students who are not already in the group
        $availableStudents = User::where('role', 2)
            ->where('school_id', $class->school_id)
            ->leftJoin('group_students', 'users.id', '=', 'group_students.student_id')
            ->whereNull('group_students.student_id')
            ->select('users.*')
            ->get();



        // Fetch available teachers similarly
        $availableTeachers = User::where('role', 1)
            ->where('school_id', $class->school_id)
            ->get();
        // dd($availableTeachers);

        // Fetch available programs (courses)
        // Ensure that 'id' is used for the 'Program' model, and 'program_id' for 'groupCourses'
        // dd();
        $availablePrograms = Program::whereNotIn('id', $class->groupCourses->pluck('program_id'))->whereIn('id', SchoolProgram::where('school_id', $class->school_id)->pluck('program_id'))->get();

        //Fetch all Groups in School
        $allSchoolClasses = Group::with([
            'groupCourses',
            'groupStudents.student',
            'groupTeachers.teacher',
            'groupTeachers.coTeacher',
            'groupTeacher.groupTeach',
            'groupcoTeachers.coTeacher'
        ])->where('school_id', $class->school_id)->get();
        // dd($allSchoolClasses);

        return view('dashboard.class.view', compact('class', 'availableStudents', 'availableTeachers', 'availablePrograms', 'allSchoolClasses', 'teacherNames', 'prg'));
    }
    public function mergeClasses(Request $request)
    {
        // dd($request->student_ids);
        $togroupCourses = GroupCourse::where('group_id', $request->toclass)->get();

        $fromgroupCourses = GroupCourse::where('group_id', $request->fromclass)->get();
        // dd($togroupCourses, $fromgroupCourses);
        $skippedStudents = [];

        foreach ($request->student_ids as $student) {

            // Check if the student already exists in the new group
            $exists = GroupStudent::where('group_id', $request->toclass)
                ->where('student_id', $student)
                ->exists();

            if (!$exists) {
                // Add student to group
                GroupStudent::create([
                    'group_id' => $request->toclass,
                    'student_id' => $student,
                ]);

                // Delete student courses from previous group
                foreach ($fromgroupCourses as $groupCourse) {
                    UserCourse::where('user_id', $student)->where('program_id', $groupCourse->program_id)->delete();
                }

                // Add new group courses to student 
                foreach ($togroupCourses as $groupCourse) {
                    UserCourse::firstOrCreate([
                        'user_id' => $student,
                        'program_id' => $groupCourse->program_id,
                    ]);
                }

                // Delete Student form previous group
                GroupStudent::where('group_id', $request->fromclass)
                    ->where('student_id', $student)
                    ->delete();
            } else {
                array_push($skippedStudents, $student);
            }
        }
        if (!empty($skippedStudents)) {
            return redirect()->back()->with('success', 'Students added to new group. However, some students were already in the group!');
        } else {
            return redirect()->back()->with('success', 'Students added to the group and assigned to all courses!');
        }
    }



    public function addGroupCourse(Request $request)
    {
        // Validate the request
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'program_id' => 'required|exists:programs,id',
        ]);

        // Check if the course is already assigned to the group
        $existingCourse = GroupCourse::where('group_id', $request->group_id)
            ->where('program_id', $request->program_id)
            ->first();

        if ($existingCourse) {
            return redirect()->back()->with('error', 'Course is already assigned to this group.');
        }

        // Assign the course to the group
        GroupCourse::create([
            'group_id' => $request->group_id,
            'program_id' => $request->program_id,
        ]);

        // Fetch all the students in the group
        $studentsInGroup = GroupStudent::where('group_id', $request->group_id)->pluck('student_id');

        // For each student in the group, assign the new course if they don't already have it
        foreach ($studentsInGroup as $studentId) {
            // Check if the student already has the course in user_courses
            UserCourse::firstOrCreate([
                'user_id' => $studentId,
                'program_id' => $request->program_id,
            ]);
        }

        return redirect()->back()->with('success', 'Course assigned to the group and added to all students.');
    }
    public function removeGroupCourse($id)
    {
        $groupCourse = GroupCourse::findOrFail($id);
        $groupId = $groupCourse->group_id;
        $programId = $groupCourse->program_id;

        // Delete the course from the group
        $groupCourse->delete();

        // Fetch all students in the group
        $studentsInGroup = GroupStudent::where('group_id', $groupId)->pluck('student_id');
        $teachersInGroup = GroupTeachers::where('group_id', $groupId)->where('program_id', $programId)->get();

        // Remove the course from each student if it's not assigned in other groups
        foreach ($studentsInGroup as $studentId) {
            $otherGroups = GroupCourse::where('program_id', $programId)
                ->whereHas('groupStudents', fn($query) => $query->where('student_id', $studentId))
                ->count();

            // If the course is not in any other group for the student, remove it
            if ($otherGroups == 0) {
                UserCourse::where('user_id', $studentId)->where('program_id', $programId)->delete();
            }
        }
        foreach ($teachersInGroup as $teacher) {
            $teacherId = $teacher->teacher_id;
            $coteacherId = $teacher->co_teacher_id;
            $deleted = $teacher->delete();
            if ($deleted) {
                if ($coteacherId != null) {
                    $otherCogroups =  $otherGroups = GroupTeachers::where('teacher_id', $coteacherId)
                        ->where('program_id', $programId)
                        ->count();
                    if ($otherCogroups == 0) {
                        TeacherProgram::where('teacher_id', $coteacherId)
                            ->where('program_id', $programId)
                            ->delete();
                    }
                }
                $otherGroups = GroupTeachers::where('teacher_id', $teacherId)
                    ->where('program_id', $programId)
                    ->count();

                // If the teacher is not assigned to this program in any other group, remove the program from TeacherProgram
                if ($otherGroups == 0) {
                    TeacherProgram::where('teacher_id', $teacherId)
                        ->where('program_id', $programId)
                        ->delete();
                }
            }
        }

        return redirect()->back()->with('success', 'Course removed from the group and from relevant students and teachers.');
    }

    public function addStudents(Request $request)
    {
        // Validate the request input
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
        ]);

        $alreadyInGroup = [];
        $addedStudents = [];

        // Fetch all the courses associated with this group
        $groupCourses = GroupCourse::where('group_id', $request->group_id)->get();

        foreach ($request->student_ids as $student_id) {
            // Check if the student is already in the group
            $existingStudent = GroupStudent::where('group_id', $request->group_id)
                ->where('student_id', $student_id)
                ->first();

            if ($existingStudent) {
                // Add to the list of students who are already in the group
                $alreadyInGroup[] = User::find($student_id)->name;
            } else {
                // Add the student to the group
                GroupStudent::create([
                    'group_id' => $request->group_id,
                    'student_id' => $student_id,
                ]);
                $addedStudents[] = User::find($student_id)->name;

                // Assign the student to all the courses in the group
                foreach ($groupCourses as $groupCourse) {
                    UserCourse::firstOrCreate([
                        'user_id' => $student_id,
                        'program_id' => $groupCourse->program_id,
                    ]);
                }
            }
        }

        // Return appropriate response with messages for both added and already existing students
        if (!empty($alreadyInGroup) && !empty($addedStudents)) {
            return redirect()->back()->with('success', 'Students added: ' . implode(', ', $addedStudents) . '. However, the following students were already in the group: ' . implode(', ', $alreadyInGroup));
        } elseif (!empty($alreadyInGroup)) {
            return redirect()->back()->with('error', 'The following students were already in the group: ' . implode(', ', $alreadyInGroup));
        } else {
            return redirect()->back()->with('success', 'Students added to the group and assigned to all courses: ' . implode(', ', $addedStudents));
        }
    }

    public function removeStudent($id)
    {
        // Find the specific GroupStudent entry using the $id
        $student = GroupStudent::findOrFail($id);
        $groupId = $student->group_id;  // Get the group this student is part of
        $studentId = $student->student_id;  // Get the student being removed

        // Fetch all the courses (programs) associated with the group
        $groupCourses = GroupCourse::where('group_id', $groupId)->pluck('program_id');

        // Delete the student from the group
        $deleted = $student->delete(); // Perform the delete operation

        // If deletion was successful, check the program associations
        if ($deleted) {
            // Loop through all the courses associated with the group
            foreach ($groupCourses as $programId) {
                // Check if the student is still assigned to this program in any other group
                $otherGroupStudentCount = GroupStudent::where('student_id', $studentId)
                    ->whereHas('group', function ($query) use ($programId) {
                        // Check for other groups with the same program assigned
                        $query->whereHas('groupCourses', function ($query) use ($programId) {
                            $query->where('program_id', $programId);
                        });
                    })
                    ->count();

                // If no other groups have the student assigned to this program, remove the UserCourse entry
                if ($otherGroupStudentCount == 0) {
                    UserCourse::where('user_id', $studentId)
                        ->where('program_id', $programId)
                        ->delete();
                }
            }

            return redirect()->back()->with('success', 'Student removed from the group.');
        } else {
            return redirect()->back()->with('error', 'Failed to remove the student from the group.');
        }
    }



    public function addTeachers(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
            'teacher_ids' => 'required|array',
            'co_teacher_ids' => 'nullable|array',
            'teacher_ids.*' => [
                'exists:users,id',
                function ($attribute, $value, $fail) use ($request) {
                    if (in_array($value, $request->co_teacher_ids ?? [])) {
                        $fail('A user cannot be assigned as both teacher and co-teacher.');
                    }
                }
            ],
            'program_ids' => 'required|array',
            'program_ids.*' => 'exists:programs,id',
        ]);
        $notAddedPrograms = '';
        foreach ($request->teacher_ids as $index => $teacher_id) {
            foreach ($request->program_ids as $program_id) {
                $program = Program::find($program_id);
                $check = GroupTeachers::where('group_id', $request->group_id)
                    ->where('teacher_id', $teacher_id)
                    ->where('program_id', $program_id)->first();

                if ($check) {
                    $teacherName = User::find($teacher_id)->name;
                    $notAddedPrograms .= $teacherName . ' : ' . $program->course->name . ' - ' . $program->stage->name . '<br>';
                }
            }
        }
        // dd($notAddedPrograms);
        if ($notAddedPrograms != '') {
            return redirect()->back()->with('error', 'The same teacher is already assigned to the programs in the group <br> ' . $notAddedPrograms);
        }

        foreach ($request->teacher_ids as $index => $teacher_id) {
            foreach ($request->program_ids as $program_id) {
                $program = Program::find($program_id);
                GroupTeachers::create([
                    'group_id' => $request->group_id,
                    'teacher_id' => $teacher_id,
                    'co_teacher_id' => $request->co_teacher_ids[$index] ?? null,
                    'program_id' => $program_id,
                    'stage_id' => $program->stage_id,
                ]);

                TeacherProgram::firstOrCreate([
                    'teacher_id' => $teacher_id,
                    'program_id' => $program_id,
                    'grade_id' => $program->stage_id,
                ]);

                if (!empty($request->co_teacher_ids[$index])) {
                    $co_teacher_id = $request->co_teacher_ids[$index];
                    TeacherProgram::firstOrCreate([
                        'teacher_id' => $co_teacher_id,
                        'program_id' => $program_id,
                        'grade_id' => $program->stage_id,
                    ]);
                }
            }
        }
        return redirect()->back()->with('success', 'Teacher(s) added to the group.');
    }

    public function removeTeacher($id)
    {
        // Find the specific GroupTeacher entry by its primary key (id)
        $teacher = GroupTeachers::findOrFail($id);
        $groupId = $teacher->group_id;
        $teacherId = $teacher->teacher_id;
        $coteacherId = $teacher->co_teacher_id;

        $programId = $teacher->program_id;

        // Delete the teacher from the group
        $deleted = $teacher->delete();

        // If the teacher was successfully deleted from the group
        if ($deleted) {
            if ($coteacherId) {
                $otherCogroups =  $otherGroups = GroupTeachers::where('teacher_id', $coteacherId)
                    ->where('program_id', $programId)
                    ->count();
                if ($otherCogroups == 0) {
                    TeacherProgram::where('teacher_id', $coteacherId)
                        ->where('program_id', $programId)
                        ->delete();
                }
            }
            // Check if the teacher is assigned to the same program in any other group
            $otherGroups = GroupTeachers::where('teacher_id', $teacherId)
                ->where('program_id', $programId)
                ->count();

            // If the teacher is not assigned to this program in any other group, remove the program from TeacherProgram
            if ($otherGroups == 0) {
                TeacherProgram::where('teacher_id', $teacherId)
                    ->where('program_id', $programId)
                    ->delete();
            }

            // Return success message
            return redirect()->back()->with('success', 'Teacher removed from the group.');
        } else {
            // If deletion failed, return an error message
            return redirect()->back()->with('error', 'Failed to remove the teacher from the group.');
        }
    }







    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        if ($user->hasRole('school')) {
            $schoolId = $user->school->id;
            $schools = School::where('id', $schoolId)->get();
            // $programs = SchoolProgram::with('course')->where('school_id', $schoolId)->get();
            $programs = Program::with('course')
                ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                ->join('courses', 'programs.course_id', '=', 'courses.id')
                ->join('stages', 'programs.stage_id', '=', 'stages.id')
                ->where('school_programs.school_id', $schoolId)
                ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                ->get();
        } else {
            $schools = School::all();
            $programs = Program::with('course')->get();
            $classes = Group::simplePaginate(10);
        }

        $stages = Stage::all();

        return view('dashboard.class.create', compact('schools', 'stages', 'programs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getProgramSchool($schoolId)
    {
        $roleCourseMapping = [
            'PracticalLife-Cordinator' => 4,
            'Math-Cordinator' => 3,
            'Culture-Cordinator' => 2,
            'Arabic-Cordinator' => 5,
            'Phonics-Cordinator' => 1,
        ];
        $user = auth()->user();
        if ($user->hasRole('school') || $user->hasRole('Admin')) {
            $courseIds = [1, 2, 3, 4, 5];
        } else {
            $userRoles = $user->getRoleNames();
            $assignedCourses = collect($roleCourseMapping)->only($userRoles->toArray());
            $courseIds = $assignedCourses->values();
        }

        $programs = Program::with(['course', 'stage'])
            ->whereHas('schoolProgram', function ($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })->whereIn('course_id', $courseIds)
            ->get();
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });
        return response()->json($programsData);
    }
    public function getProgramsGroup($groupId)
    {
        $roleCourseMapping = [
            'PracticalLife-Cordinator' => 4,
            'Math-Cordinator' => 3,
            'Culture-Cordinator' => 2,
            'Arabic-Cordinator' => 5,
            'Phonics-Cordinator' => 1,
        ];
        $user = auth()->user();

        if ($user->hasRole('school') || $user->hasRole('Admin')) {
            $courseIds = [1, 2, 3, 4, 5];
        } else {
            $userRoles = $user->getRoleNames();
            $assignedCourses = collect($roleCourseMapping)->only($userRoles->toArray());
            $courseIds = $assignedCourses->values();
        }

        $programs =  Program::whereIn('id', Group::with(['groupCourses'])->findOrFail($groupId)->groupCourses->pluck('program_id'))
            ->whereIn('course_id', $courseIds)->get();
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });
        return response()->json($programsData);
    }
    public function getCommonGroupsPrograms(Request $request, $groupIds)
    {
        // Convert the comma-separated group IDs into an array
        $groupIdsArray = explode(',', $groupIds);

        // Initialize a variable to store the intersection of program IDs
        $commonProgramIds = null;

        foreach ($groupIdsArray as $groupId) {
            $programIds = Group::with('groupCourses')
                ->findOrFail($groupId)
                ->groupCourses
                ->pluck('program_id')
                ->toArray();

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
        $roleCourseMapping = [
            'PracticalLife-Cordinator' => 4,
            'Math-Cordinator' => 3,
            'Culture-Cordinator' => 2,
            'Arabic-Cordinator' => 5,
            'Phonics-Cordinator' => 1,
        ];
        $user = auth()->user();
        if ($user->hasRole('school') || $user->hasRole('Admin')) {
            $courseIds = [1, 2, 3, 4, 5];
        } else {
            $userRoles = $user->getRoleNames();
            $assignedCourses = collect($roleCourseMapping)->only($userRoles->toArray());
            $courseIds = $assignedCourses->values();
        }
        // Fetch the common programs
        $programs = Program::whereIn('id', $commonProgramIds)->whereIn('course_id', $courseIds)->get();

        // Format the program data for the response
        $programsData = $programs->map(function ($program) {
            return [
                'id' => $program->id,
                'program_details' => $program->course->name . ' - ' . $program->stage->name,
            ];
        });

        return response()->json($programsData);
    }



    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'sec_name' => 'nullable|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'program_id' => 'required|array',
            'program_id.*' => 'exists:programs,id', // Each program_id must exist in programs table
        ]);
        $grp = Group::create([
            'name' => $request->name,
            'sec_name' => $request->sec_name,
            'school_id' => $request->school_id,
        ]);
        // Loop through each selected program and create a class
        foreach ($request->program_id as $programId) {
            // Find the program and get the stage_id
            $group_classes = new GroupCourse();
            $group_classes->program_id = $programId;
            $group_classes->group_id = $grp->id;
            $group_classes->save();
        }
        $redirectUrl = session('classes_previous_url', route('classes.index'));
        return redirect($redirectUrl)->with('success', 'Class created successfully for selected programs.');
        // return redirect()->route('classes.index')->with('success', 'Class created successfully for selected programs.');
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
        $class = Group::findOrFail($id);
        // $schools = School::all();
        $programs = Program::all();
        $stages = Stage::all();

        return view("dashboard.class.edit", compact(["class", 'stages', 'programs']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'sec_name' => 'nullable|string|max:255',
        ]);

        $class = Group::findOrFail($id);
        $class->update([
            'name' => $request->name,
            'sec_name' => $request->sec_name,
        ]);
        $redirectUrl = session('classes_previous_url', route('classes.index'));
        return redirect($redirectUrl)->with('success', 'Class updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $class = Group::findOrFail($id);

        $class->delete();
        $redirectUrl = session('classes_previous_url', route('classes.index'));
        return redirect($redirectUrl)->with('success', 'Class deleted successfully.');
    }


    public function massDestroy(Request $request)
    {
        $ids = $request->input('ids');
        if ($ids) {
            Group::whereIn('id', $ids)->delete();
            $redirectUrl = session('classes_previous_url', route('classes.index'));
            return redirect($redirectUrl)->with('success', 'Classes deleted successfully.');
        }
        return redirect()->route('classes.index')->with('error', 'No Teachers selected.');
    }

    public function getStages($program_id)
    {
        $program = Program::findOrFail($program_id);
        $stage = Stage::findOrFail($program->stage_id);
        // dd($stage);
        return $stage;
    }
}
