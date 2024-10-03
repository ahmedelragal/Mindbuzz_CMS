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
                        @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">Teachers</h3>
                                    </div>
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle"><a href="#"
                                                class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                            <div class="toggle-expand-content" data-content="more-options">

                                                <form method="GET" action="{{ route('instructors.index') }}">
                                                    @csrf
                                                    <ul class="nk-block-tools d-flex justify-content-between">
                                                        @role('Admin')
                                                        <li>
                                                            <div class="drodown">
                                                                <select name="school" class="form-select" id="school_id"
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
                                                        </li>
                                                        @endrole
                                                        <li>
                                                            <div class="drodown">
                                                                <select name="program" class="form-select"
                                                                    onchange="this.form.submit()">
                                                                    <option value="">Select Program</option>
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
                                                        <!-- <li>
                                                            <div class="drodown">
                                                                <select name="grade" class="form-select"
                                                                    onchange="this.form.submit()">
                                                                    <option value="">Select Grade</option>
                                                                    @foreach ($grades as $grade)
                                                                    <option value="{{ $grade->id }}"
                                                                        {{ request('grade') == $grade->id ? 'selected' : '' }}>
                                                                        {{ $grade->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </li> -->
                                                        <li>
                                                            <div class="drodown">
                                                                <select name="group" class="form-select"
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
                                                        </li>

                                                    </ul>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nk-block">

                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr>
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
                                            <th scope="row">


                                                <div class="nk-tb-col">
                                                    <div class="user-card">
                                                        <div class="user-avatar"> <img
                                                                src="{{ $instructor->parent_image }}"
                                                                alt="">


                                                        </div>
                                                        <div class="user-info"><span
                                                                class="tb-lead">{{ $instructor->name }}
                                                                <span
                                                                    class="dot dot-warning d-md-none ms-1"></span></span><br><span>{{ $instructor->email }}</span>
                                                        </div>
                                                    </div>

                                                </div>
                                            </th>
                                            <td>{{ $instructor->school->name ?? '-' }}
                                            </td>
                                            <div class="d-lg-flex d-none">

                                            </div>

                                            <td>{{ $instructor->phone }}</td>
                                            <td>

                                                <div class="d-lg-flex d-none">
                                                    <div class="drodown"><a href="#"
                                                            class="dropdown-toggle pt-1 text-info"
                                                            data-bs-toggle="dropdown"> <button
                                                                class="btn btn-gray">View
                                                            </button> </a>

                                                        <div class="dropdown-menu dropdown-menu-start">
                                                            <ul class="link-list-opt no-bdr p-3">
                                                                @foreach ($instructor->teacher_programs as $course)
                                                                <li class="tb-lead p-1">
                                                                    {{ $course->program->course->name  ?? 'N/A' }}
                                                                    /
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
                                                    <div class="col-4 "><a
                                                            href="{{ route('instructors.edit', $instructor->id) }}"
                                                            class="btn btn-warning me-2">Edit</a>
                                                    </div>
                                                    <div class="col-1"></div>
                                                    <div class="col-5 ">
                                                        <form id="delete-form-{{ $instructor->id }}"
                                                            action="{{ route('instructors.destroy', $instructor->id) }}"
                                                            method="POST" style="display:inline-block;">
                                                            @csrf
                                                            @method('DELETE')


                                                            <div class="d-lg-flex d-none">

                                                            </div>

                                                        </form>
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="confirmDelete({{ $instructor->id }})">Delete</button>
                                                    </div>
                                                </div>



                                            </td>
                                        </tr>

                            </div>
                            @endforeach



                            </tbody>
                            </table>



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
@endsection
@section('page_js')
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
            }
        })
    }
</script>
@endsection