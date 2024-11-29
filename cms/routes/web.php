
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Dashboard\ClassController;
use App\Http\Controllers\Dashboard\CourseController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\InstructorController;
use App\Http\Controllers\Dashboard\ProgramController;
use App\Http\Controllers\Dashboard\SchoolController;
use App\Http\Controllers\Dashboard\StageController;
use App\Http\Controllers\Dashboard\StudentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Dashboard\UserController;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});
Route::get('classes/{id}/view', [ClassController::class, 'view'])->name('classes.view');

Route::post('classes/merge', [ClassController::class, 'mergeClasses'])->name('classes.merge');
Route::post('students/add', [ClassController::class, 'addStudents'])->name('students.add');
Route::post('teachers/add', [ClassController::class, 'addTeachers'])->name('teachers.add');

Route::delete('students/{id}/remove', [ClassController::class, 'removeStudent'])->name('students.remove');

Route::delete('teachers/{id}/remove', [ClassController::class, 'removeTeacher'])->name('teachers.remove');

Route::post('groupCourses/add', [ClassController::class, 'addGroupCourse'])->name('groupCourses.add');
Route::delete('groupCourses/{id}/remove', [ClassController::class, 'removeGroupCourse'])->name('groupCourses.remove');


Route::get('get-courses/{id}/{schoolId}', [StudentController::class, 'getCourses']);
Route::get('/get-groups-student/{school_id}', [StudentController::class, 'getGroups']);
Route::get('/get-programs-school/{school_id}', [ClassController::class, 'getProgramSchool']);
Route::get('/get-programs-group/{group_id}', [ClassController::class, 'getProgramsGroup']);
Route::get('/get-students-school/{school_id}', [SchoolController::class, 'getStudentsSchool']);
Route::get('/get-teachers-school/{school_id}', [SchoolController::class, 'getTeachersSchool']);
Route::get('/get-common-programs-group/{groupIds}', [ClassController::class, 'getCommonGroupsPrograms']);
Route::get('/get-common-programs-teacher/{teacher1_id}/{teacher2_id}', [InstructorController::class, 'getCommonTeacherPrograms']);
Route::get('/get-teacher-programs/{teacher_id}', [InstructorController::class, 'getTeacherPrograms']);
Route::get('/get-common-programs-student/{student1_id}/{student2_id}', [StudentController::class, 'getCommonStudentPrograms']);
Route::get('/get-student-programs/{student_id}', [StudentController::class, 'getStudentPrograms']);

Route::get('get-groups/{school_id}', [InstructorController::class, 'getGroups'])->name('getGroups');
Route::get('get-stages/{program_id}', [ClassController::class, 'getStages']);
Route::get('/get-programs/{school_id}/{stage_id}', [InstructorController::class, 'getPrograms']);
// Authenticated Routes 
Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::get("/", [DashboardController::class, 'index'])->name("dashboard");
    Route::get('get-duplicate-classes/{class_id}/{school_id}', [StudentController::class, 'getDuplicateClasses'])->name('getDuplicateClasses');
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('completionReport', [ReportController::class, 'completionReport'])->name('completionReport');
        Route::get('masteryReport', [ReportController::class, 'masteryReport'])->name('masteryReport');
        Route::get('numOfTrialsReport', [ReportController::class, 'numOfTrialsReport'])->name('numOfTrialsReport');
        Route::get('skillReport', [ReportController::class, 'skillReport'])->name('skillReport');
        Route::get('select-group', [ReportController::class, 'selectGroup'])->name('selectGroup');
        Route::get('class-completion-report', [ReportController::class, 'classCompletionReportWeb'])->name('classCompletionReportWeb');
        Route::get('class-mastery-report', [ReportController::class, 'classMasteryReportWeb'])->name('classMasteryReportWeb');
        Route::get('class-num-of-trials-report', [ReportController::class, 'classNumOfTrialsReportWeb'])->name('classNumOfTrialsReportWeb');
        Route::get('class-gender-report', [ReportController::class, 'classGenderReportWeb'])->name('classGenderReportWeb');
        Route::get('school-gender-report', [ReportController::class, 'schoolGenderReportWeb'])->name('schoolGenderReportWeb');
        Route::get('studentLoginReport', [ReportController::class, 'studentLoginReport'])->name('studentLoginReport');
        Route::get('teacherLoginReport', [ReportController::class, 'teacherLoginReport'])->name('teacherLoginReport');
        Route::get('teacherCompletionReport', [ReportController::class, 'teacherCompletionReport'])->name('teacherCompletionReport');
        Route::get('schoolCompletionReport', [ReportController::class, 'schoolCompletionReport'])->name('schoolCompletionReport');
        Route::get('teacherStudentsMasteryLevel', [ReportController::class, 'teacherStudentsMasteryLevel'])->name('teacherStudentsMasteryLevel');
        Route::get('classLoginReport', [ReportController::class, 'classLoginReport'])->name('classLoginReport');
        Route::get('schoolLoginReport', [ReportController::class, 'schoolLoginReport'])->name('schoolLoginReport');
        Route::get('schoolContentEngagementReport', [ReportController::class, 'schoolContentEngagementReport'])->name('schoolContentEngagementReport');
        Route::get('classContentEngagementReport', [ReportController::class, 'classContentEngagementReport'])->name('classContentEngagementReport');
        Route::get('studentContentEngagementReport', [ReportController::class, 'studentContentEngagementReport'])->name('studentContentEngagementReport');
        Route::get('classContentUsageReport', [ReportController::class, 'classContentUsageReport'])->name('classContentUsageReport');
        Route::get('teacherContentCoverageReport', [ReportController::class, 'teacherContentCoverageReport'])->name('teacherContentCoverageReport');
        Route::get('classContentGapReport', [ReportController::class, 'classContentGapReport'])->name('classContentGapReport');
        Route::get('schoolContentGapReport', [ReportController::class, 'schoolContentGapReport'])->name('schoolContentGapReport');
        Route::get('teacherContentEngagementReport', [ReportController::class, 'teacherContentEngagementReport'])->name('teacherContentEngagementReport');
        Route::get('classHeatmapReport', [ReportController::class, 'classHeatmapReport'])->name('classHeatmapReport');
        Route::get('teacherHeatmapReport', [ReportController::class, 'teacherHeatmapReport'])->name('teacherHeatmapReport');
        Route::get('studentHeatmapReport', [ReportController::class, 'studentHeatmapReport'])->name('studentHeatmapReport');
    });
    Route::get('/get-teacher-assignments/{teacherId}', [ReportController::class, 'getTeacherAssignments']);
    Route::get('/reports/fetch-mastery-data', [ReportController::class, 'fetchMasteryData'])->name('reports.fetchMasteryData');
    Route::post('/get-lessons-by-units', [ProgramController::class, 'getLessonsByUnits'])->name('get.lessons.by.units');
    Route::post('/get-units-by-program', [ProgramController::class, 'getUnitsByProgram'])->name('get.units.by.program');

    Route::resource('students', StudentController::class);
    Route::delete('/students-selected/mass-delete', [StudentController::class, 'massDestroy'])->name('students.massDestroy');
    Route::delete('/teachers-selected/mass-delete', [InstructorController::class, 'massDestroy'])->name('teachers.massDestroy');
    Route::delete('/classes-selected/mass-delete', [ClassController::class, 'massDestroy'])->name('classes.massDestroy');
    Route::delete('/schools-selected/mass-delete', [SchoolController::class, 'massDestroy'])->name('schools.massDestroy');
    Route::delete('/users-selected/mass-delete', [UserController::class, 'massDestroy'])->name('users.massDestroy');

    Route::get('/users/makeadmin/{userId}', [UserController::class, 'makeAdmin'])->name('users.makeAdmin');
    Route::get('/users/removeAdmin/{userId}', [UserController::class, 'removeAdmin'])->name('users.removeAdmin');
    Route::get('/users/makeschooladmin/', [UserController::class, 'makeSchoolAdmin'])->name('users.makeSchoolAdmin');
    Route::get('/users/removeschoolAdmin/{userId}', [UserController::class, 'removeSchoolAdmin'])->name('users.removeSchoolAdmin');

    Route::post('import-users', [StudentController::class, 'import'])->name('import.users');
    Route::resource('instructors', InstructorController::class);
    Route::resource('schools', SchoolController::class);
    // Route::resource('courses', CourseController::class);
    Route::resource('stages', StageController::class);
    Route::resource('classes', ClassController::class);
    Route::resource('programs', ProgramController::class);
    Route::any('programs.addcurriculum/{id}', [ProgramController::class, 'addcurriculum'])->name('add-curriculum');
    Route::any('programs.viewcurriculum/{id}', [ProgramController::class, 'viewcurriculum'])->name('view-curriculum');
    Route::resource('users', UserController::class);
    Route::delete('curriculum/remove/{schoolid}/{programid}', [ProgramController::class, 'removecurriculum'])->name('curriculum.remove');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');


    Route::resource('roles', RoleController::class);
});


Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');
