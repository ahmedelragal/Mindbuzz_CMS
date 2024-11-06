@php
session(['teachers_previous_url' => url()->full()]);
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
                                <div>
                                    <div class="nk-block-head-content" style="display:flex; align-items:center; justify-content:space-between">
                                        <h3 class="nk-block-title page-title">Teacher List</h3>
                                        <div class="nk-block-head nk-block-head-sm" style="margin-top: 20px;">
                                            <div class="nk-block-head-content" style="display:flex; gap:10px;justify-content:space-between">
                                                <div class="toggle-wrap nk-block-tools-toggle">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                        data-target="more-options">
                                                        <em class="icon ni ni-more-v"></em>
                                                    </a>
                                                    <div class="toggle-expand-content " data-content="more-options">
                                                        <form method="GET" action="{{ route('instructors.index') }}">
                                                            <ul class="nk-block-tools d-flex justify-content-between" style="display:flex; gap:10px;">
                                                                @role('Admin')
                                                                <li>
                                                                    <div class="drodown" style="width:190px;">
                                                                        <select name="school" class="form-select js-select2" id="school_id" style="width:170px;"
                                                                            onchange="this.form.submit()">
                                                                            <option value="">All Schools</option>
                                                                            @foreach ($schools as $school)
                                                                            <option value="{{ $school->id }}"
                                                                                {{ request('school') == $school->id ? 'selected' : '' }}>
                                                                                {{ $school->name }}
                                                                            </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </li>
                                                                @endrole
                                                                <li>
                                                                    <div class="drodown" style="width:190px;">
                                                                        <select name="program" class="form-select js-select2"
                                                                            onchange="this.form.submit()">
                                                                            <option value="">All Programs</option>
                                                                            @role('Admin')
                                                                            @foreach ($programs as $program)
                                                                            <option value="{{ $program->id }}"
                                                                                {{ request('program') == $program->id ? 'selected' : '' }}>
                                                                                @if ($program && $program->course)
                                                                                {{ $program->course->name . '/' . $program->stage->name }}
                                                                                @else
                                                                                {{ $program->name }}
                                                                                @endif
                                                                            </option>
                                                                            @endforeach
                                                                            @endrole
                                                                            @role('school')
                                                                            @php
                                                                            $sch_programs = App\Models\Program::with('course')
                                                                            ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                                                                            ->join('courses', 'programs.course_id', '=', 'courses.id')
                                                                            ->join('stages', 'programs.stage_id', '=', 'stages.id')
                                                                            ->where('school_programs.school_id', auth()->user()->school_id)
                                                                            ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                                                                            ->get();
                                                                            @endphp
                                                                            @foreach ($sch_programs as $program)
                                                                            <option value="{{ $program->id }}"
                                                                                {{ request('program') == $program->id ? 'selected' : '' }}>
                                                                                @if ($program && $program->course)
                                                                                {{ $program->course->name . '/' . $program->stage->name }}
                                                                                @else
                                                                                {{ $program->name }}
                                                                                @endif
                                                                            </option>
                                                                            @endforeach
                                                                            @endrole
                                                                        </select>
                                                                    </div>
                                                                </li>

                                                                <li>
                                                                    <div class="drodown" style="width:190px;">
                                                                        <select name="group" class="form-select js-select2" style="width:170px;"
                                                                            onchange="this.form.submit()">
                                                                            <option value="">All Classes</option>
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
                                                                </li>
                                                                <li>
                                                                    <button type="button" onclick="massDelete()" class="btn btn-primary">Delete Selected</button>
                                                                </li>
                                                            </ul>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>




                            <div class="nk-block">
                                <form id="mass-delete-form" action="{{ route('teachers.massDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col"><input type="checkbox" id="select-all"></th>
                                                <th scope="col">Teacher</th>
                                                <th scope="col">School</th>
                                                <th scope="col">Phone</th>
                                                <th scope="col">Program</th>
                                                <th scope="col" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($instructors as $instructor)
                                            <tr>
                                                <td><input type="checkbox" class="teacher-checkbox" name="ids[]" value="{{ $instructor->id }}"></td>
                                                <th scope="row">
                                                    <div class="nk-tb-col">
                                                        <div class="user-card">
                                                            <div class="user-avatar"><img src="../images/avatar/a-sm.jpg" alt=""></div>
                                                            <div class="user-info">
                                                                <span class="tb-lead">{{ $instructor->name }}<br><span>{{ $instructor->email }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </th>
                                                <td>{{ $instructor->school->name ?? '-' }}
                                                <td>{{ $instructor->phone }}</td>
                                                <td>
                                                    <div class="d-lg-flex d-none">
                                                        <div class="drodown"><a href="#" class="dropdown-toggle pt-1 text-info" data-bs-toggle="dropdown"> <button class="btn btn-gray">View</button> </a>
                                                            <div class="dropdown-menu dropdown-menu-start">
                                                                <ul class="link-list-opt no-bdr p-3">
                                                                    @if ($instructor->teacher_programs->isEmpty())

                                                                    <li><span>No Available Programs</span></li>
                                                                    @else
                                                                    @foreach ($instructor->teacher_programs as $course)
                                                                    <li class="tb-lead p-1">
                                                                        {{ $course->program->course->name  ?? 'N/A' }} /
                                                                        {{ $course->program->stage->name  ?? 'N/A' }}
                                                                        @if (!$loop->last)
                                                                        ,
                                                                        @endif
                                                                    </li>
                                                                    @endforeach
                                                                    @endif

                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="row w-90">
                                                        <div class="col-4 ">
                                                            <a href="{{ route('instructors.edit', $instructor->id) }}" class="btn btn-warning me-1">Edit</a>
                                                        </div>
                                                        <div class="col-1"></div>
                                                        <div class="col-5 ">
                                                            <form id="delete-form-{{ $instructor->id }}" action="{{ route('instructors.destroy', $instructor->id) }}" method="POST" style="display: none;">
                                                                @csrf
                                                                @method('DELETE')
                                                            </form>
                                                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $instructor->id }})">Delete</button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>



                                <div class="card-inner">
                                    {!! $instructors->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
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
    function confirmDelete(instructorId) {
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
                document.getElementById('delete-form-' + instructorId).submit();
                return;
            }
        })
    }
</script>
<script>
    function massDelete() {
        const selectedTeachers = document.querySelectorAll('.teacher-checkbox:checked');
        if (selectedTeachers.length === 0) {
            Swal.fire({
                title: 'Delete Teachers',
                text: "Please select at least one teacher to delete.",
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

    }
</script>
@endsection