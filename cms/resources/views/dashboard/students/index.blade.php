@php
session(['students_previous_url' => url()->full()]);
@endphp
@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap">
            @include('dashboard.layouts.navbar')
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">Student List</h3>
                                    </div>
                                </div>
                            </div>


                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-head-content" style="display:flex; gap:10px;justify-content:space-between">
                                    <div class="toggle-wrap nk-block-tools-toggle">
                                        <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1"
                                            data-target="more-options">
                                            <em class="icon ni ni-more-v"></em>
                                        </a>
                                        <div class="toggle-expand-content " data-content="more-options">
                                            <form method="GET" action="{{ route('students.index') }}">

                                                @role('Admin')
                                                <div style="display:flex; gap:10px;">
                                                    <div class="drodown" style="width:190px;">
                                                        <select name="school" class="form-select js-select2" id="school_id"
                                                            onchange="this.form.submit()">
                                                            <option value="">Select School</option>
                                                            @foreach ($schools as $school)
                                                            <option value="{{ $school->id }}"
                                                                {{ request('school') == $school->id ? 'selected' : '' }}>
                                                                {{ $school->name }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    @endrole

                                                    <div class="drodown" style="width:190px;">
                                                        <select name="group" class="form-select js-select2"
                                                            onchange="this.form.submit()">
                                                            <option value="">Select a class</option>
                                                            @role('Admin')
                                                            @foreach ($classes as $class)
                                                            <option value="{{ $class->id }}"
                                                                {{ request('group') == $class->id ? 'selected' : '' }}>
                                                                {{ $class->name }}
                                                            </option>
                                                            @endforeach
                                                            @endrole
                                                            @role('school')
                                                            @php
                                                            $groups = App\Models\Group::where('school_id', auth()->user()->school_id)
                                                            ->with(['program', 'program.course', 'program.stage'])
                                                            ->get();
                                                            @endphp
                                                            @foreach ($groups as $class)
                                                            <option value="{{ $class->id }}"
                                                                {{ request('group') == $class->id ? 'selected' : '' }}>
                                                                {{ $class->name }}
                                                            </option>
                                                            @endforeach
                                                            @endrole
                                                        </select>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                    <div style="justify-content :right; display: flex ;gap:10px">
                                        <button type="button" onclick="massDelete()" class="btn btn-primary">Delete Selected</button>
                                        <button type="button" class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#importStudentsModal">
                                            Import
                                        </button>
                                    </div>
                                </div>

                            </div>
                            <!-- Modal for adding students -->
                            <div class="modal fade" id="importStudentsModal" tabindex="-1" aria-labelledby="importStudentsModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header text-white" style="background-color: #364a63;">
                                            <h5 class="modal-title" id="importStudentsModalLabel">Add Students</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3">
                                                    @role('Admin')
                                                    <label for="school_id" class="form-label">Select School:</label>
                                                    <select name="school_id" id="school_id" class="form-select" required>
                                                        <option value="" disabled selected>Select a school</option>
                                                        @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}"
                                                            {{ request('school') == $school->id ? 'selected' : '' }}>
                                                            {{ $school->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @endrole
                                                    @role('school')
                                                    <input type="hidden" name="school_id" value="{{auth()->user()->school_id}}">
                                                    @endrole
                                                </div>

                                                <div class="mb-3">
                                                    <label for="file" class="form-label">Upload Excel File:</label>
                                                    <input type="file" name="file" id="file" class="form-control" accept=".xlsx, .xls" required>
                                                    <div class="form-text">Please upload an Excel file (.xlsx or .xls) containing the student details.</div>
                                                </div>

                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Import Students</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="nk-block">
                                <form id="mass-delete-form" action="{{ route('students.massDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col"><input type="checkbox" id="select-all"></th>
                                                <th scope="col">Student</th>
                                                <th scope="col">School</th>
                                                <th scope="col">Phone</th>
                                                <th scope="col">Program</th>
                                                <th scope="col" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($students as $student)
                                            <tr>
                                                <td><input type="checkbox" class="student-checkbox" name="ids[]" value="{{ $student->id }}"></td>
                                                <th scope="row">
                                                    <div class="nk-tb-col">
                                                        <div class="user-card">
                                                            <div class="user-avatar"><img src="../images/avatar/a-sm.jpg" alt=""></div>
                                                            <div class="user-info">
                                                                <span class="tb-lead">{{ $student->name }}<br><span>{{ $student->email }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </th>
                                                <td>{{ $student->school->name }}</td>
                                                <td>{{ $student->phone }}</td>
                                                <td>
                                                    <div class="d-lg-flex d-none">
                                                        <div class="drodown"><a href="#" class="dropdown-toggle pt-1 text-info" data-bs-toggle="dropdown"> <button class="btn btn-gray">View</button> </a>
                                                            <div class="dropdown-menu dropdown-menu-start">
                                                                <ul class="link-list-opt no-bdr p-3">
                                                                    @foreach ($student->userCourses as $course)
                                                                    <li class="tb-lead p-1">
                                                                        {{ $course->program->course->name  ?? 'N/A' }} /
                                                                        {{ $course->program->stage->name  ?? 'N/A' }}
                                                                        @if (!$loop->last)
                                                                        ,
                                                                        @endif
                                                                    </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="row w-90">
                                                        <div class="col-4 ">
                                                            <a href="{{ route('students.edit', $student->id) }}" class="btn btn-warning me-1">Edit</a>
                                                        </div>
                                                        <div class="col-1"></div>
                                                        <div class="col-5 ">
                                                            <form id="delete-form-{{ $student->id }}" action="{{ route('students.destroy', $student->id) }}" method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $student->id }})">Delete</button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>



                                <div class="card-inner">
                                    {!! $students->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
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
@section('page_js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).ready(function() {
            // Initialize select2 for the filters
            $('.js-select2').select2();
        });
    });
</script>

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
<script>
    function confirmDelete(studentId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + studentId).submit();
            }
        })
    }
</script>

<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    function massDelete() {
        const selectedStudents = document.querySelectorAll('.student-checkbox:checked');
        if (selectedStudents.length === 0) {
            // alert('Please select at least one student to delete.');
            // return;

            Swal.fire({
                title: 'Delete Students',
                text: "Please select at least one student to delete.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });

            return;
        }
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('mass-delete-form').submit();
            }
        })
        // if (confirm('Are you sure you want to delete the selected students?')) {
        //     document.getElementById('mass-delete-form').submit();
        // }
    }
</script>
@endsection