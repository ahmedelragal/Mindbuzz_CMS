@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap ">
            @include('dashboard.layouts.navbar')

            <div class="nk-content ">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">Class Details - {{ $class->name }}</h3>
                                    </div>
                                    <div>

                                        @can('class-edit')
                                        <a href="{{ route('classes.edit', $class->id) }}" class="btn btn-primary me-1">Edit Name</a>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                            <!-- Class Information -->
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Class Information</h5>
                                    <button type="button" class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#manageCoursesModal">
                                        Manage Courses
                                    </button>
                                </div>
                                <div class="card-body">
                                    <p><strong>Class Name: </strong>{{ $class->name }}</p>
                                    <!-- <p><strong>Secondary Name: </strong>{{ $class->sec_name }}</p> -->
                                    <p><strong>School: </strong>{{ $class->school->name ?? 'No School' }}</p>
                                </div>
                            </div>



                            <!-- Modal for managing group courses -->
                            <div class="modal fade" id="manageCoursesModal" tabindex="-1" aria-labelledby="manageCoursesModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="manageCoursesModalLabel">Manage Courses for {{ $class->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <h5>Current Courses</h5>
                                            <ul class="list-group mb-4">
                                                @foreach($class->groupCourses as $course)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    {{ $course->program->course->name }} - {{ $course->program->stage->name }}
                                                    <form action="{{ route('groupCourses.remove', $course->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                                    </form>
                                                </li>
                                                @endforeach
                                            </ul>

                                            <h5 class="mt-4">Add a New Course</h5>
                                            <form action="{{ route('groupCourses.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $class->id }}">
                                                <div class="mb-3">
                                                    <label for="program" class="form-label">Select Program (Course)</label>
                                                    <select name="program_id" class="form-select" required>
                                                        @foreach($availablePrograms as $program)
                                                        <option value="{{ $program->id }}">{{ $program->course->name }} - {{ $program->stage->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Assign Course</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Teachers Table -->
                            <div class="card mt-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Teachers in Class</h5>
                                    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add Teachers</button>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Teacher Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($prg as $index => $courses)
                                            <tr>
                                                <td>{{ $index }}</td>
                                                <td>
                                                    <button
                                                        class="btn btn-info float-right"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewTeacherPrograms-{{ $loop->index }}"
                                                        data-teacher-name="{{ $index }}"
                                                        data-programs="{{ json_encode($courses) }}">View Programs</button>


                                                    <!-- Modal for viewing teacher programs -->
                                                    <div class="modal fade" id="viewTeacherPrograms-{{ $loop->index }}" tabindex="-1" aria-labelledby="viewTeacherProgramsLabel" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="viewTeacherProgramsLabel">View Programs for {{ $index }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <table class="table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Co-Teacher Name</th>
                                                                                <th>Program</th>
                                                                                <th>Stage</th>
                                                                                <th>Action</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody id="programs-table-body-{{ $loop->index }}">
                                                                            <!-- Content will be injected by JavaScript -->


                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>



                            <!-- Students Table -->
                            <div class="card mt-4">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5>Students in Class</h5>
                                    <div>
                                        <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add Students</button>
                                        <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#mergeClassesModal" style="margin-left:5px;">Merge Classes</button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($class->groupStudents as $student)
                                            <tr>
                                                <td>{{ $student->student->name }}</td>
                                                <td>
                                                    <form action="{{ route('students.remove', $student->id) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Remove</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Modal for merging classes -->
                            <div class="modal fade" id="mergeClassesModal" tabindex="-1" aria-labelledby="mergeClassesModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="mergeClassesModalLabel">Merge Students</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('classes.merge') }}" method="POST">
                                                @csrf
                                                <!-- Select Multiple Students -->
                                                <div class="mb-3">
                                                    <label for="studentName" class="form-label">Select Students</label>
                                                    <select name="student_ids[]" class="form-select" multiple required>
                                                        @foreach($class->groupStudents as $student)
                                                        <option value="{{ $student->student->id }}">{{ $student->student->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="FromClass" class="form-label">From Class</label>
                                                    <select name="fromclass" class="form-select" required>
                                                        <option value="{{ $class->id }}">{{$class->name}}</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="ToClass" class="form-label">To Class</label>
                                                    <select name="toclass" class="form-select" required>
                                                        @foreach($allSchoolClasses as $currClass)
                                                        @if ($currClass->id != $class->id)
                                                        <option value="{{ $currClass->id }}">{{ $currClass->name }}</option>
                                                        @endif
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Merge Classes</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal for adding students -->
                            <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addStudentModalLabel">Add Students</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('students.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $class->id }}">

                                                <!-- Select Multiple Students -->
                                                <div class="mb-3">
                                                    <label for="studentName" class="form-label">Select Students</label>
                                                    <select name="student_ids[]" class="form-select" multiple required>
                                                        @foreach($availableStudents as $student)
                                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Add Students</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal for adding teachers -->
                            <div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="addTeacherModalLabel">Add Teachers and Co-Teachers</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('teachers.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="group_id" value="{{ $class->id }}">

                                                <!-- Select Multiple Teachers -->
                                                <div class="mb-3">
                                                    <label for="teacherName" class="form-label">Select Teachers</label>
                                                    <select name="teacher_ids[]" class="form-select" multiple required>
                                                        @foreach($availableTeachers as $teacher)
                                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Select Multiple Co-Teachers (Optional) -->
                                                <div class="mb-3">
                                                    <label for="coTeacherName" class="form-label">Select Co-Teachers</label>
                                                    <select name="co_teacher_ids[]" class="form-select" multiple>
                                                        <option value="">None</option>
                                                        @foreach($availableTeachers as $teacher)
                                                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <!-- Select Multiple Group Courses (Programs) -->
                                                <div class="mb-3">
                                                    <label for="program_id" class="form-label">Select Courses</label>
                                                    <select name="program_ids[]" class="form-select" multiple required>
                                                        @foreach($class->groupCourses as $groupCourse)
                                                        <option value="{{ $groupCourse->program_id }}">
                                                            {{ $groupCourse->program->course->name }} - {{ $groupCourse->program->stage->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Add Teachers</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all buttons with a modal target
        document.querySelectorAll('[data-bs-target^="#viewTeacherPrograms"]').forEach(button => {
            button.addEventListener('click', function() {
                // Retrieve data from button attributes
                const teacherName = this.getAttribute('data-teacher-name');
                const programs = JSON.parse(this.getAttribute('data-programs'));
                const modalIndex = this.getAttribute('data-bs-target').split('-').pop();

                const tableBody = document.getElementById('programs-table-body-' + modalIndex);
                tableBody.innerHTML = ''; // Clear previous content

                // Populate modal with data
                programs.forEach(course => {
                    const row = document.createElement('tr');

                    // Create form with remove button
                    const formHtml = `
                        <form action="{{ route('teachers.remove', ':id') }}" method="POST"> 
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </form>
                    `.replace(':id', course.id); // Replace placeholder with actual ID

                    row.innerHTML = `
                        <td>${course.coTeacher || 'None'}</td>
                        <td>${course.course}</td>
                        <td>${course.stage}</td>
                        <td>${formHtml}</td>
                    `;

                    tableBody.appendChild(row);
                });
            });
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@section('page_js')
<!-- SweetAlert validation messages -->
@if($errors->any())
<script>
    Swal.fire({
        title: 'Error!',
        text: '{{ implode('\
        n ', $errors->all()) }}',
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif

@if(session('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonText: 'Ok'
    });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({
        title: 'Error!',
        text: '{{ session('
        error ') }}',
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif
@endsection