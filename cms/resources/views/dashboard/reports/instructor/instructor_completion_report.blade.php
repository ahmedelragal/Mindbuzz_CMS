@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        <!-- Sidebar -->
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap">
            <!-- Navbar -->
            @include('dashboard.layouts.navbar')
            <!-- Main Content -->
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="title">Teacher Completion Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.teacherCompletionReport') }}">
                                        @csrf
                                        <div class="row">
                                            <!-- School Filter -->
                                            <div class="col-md-6">
                                                @role('Admin')
                                                <label for="sch_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="sch_id">
                                                    <option value="" selected disabled>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole

                                                @role('school')
                                                <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
                                                @endrole
                                            </div>

                                            <div class="col-md-6">
                                                <label for="teacher_id">Select Teacher</label>
                                                <select class="form-select js-select2" name="teacher_id" id="teacher_id">
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Teacher</option>
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schTeachers = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schTeachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ old('teacher_id', $request['teacher_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>


                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id" required>
                                                    <option value="" disabled selected>Choose a Program</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="assignment_id">Select Assignment</label>
                                                <select class="form-select js-select2" name="assignment_id" id="assignment_id">
                                                    <option value="" disabled selected>All Assignments</option>
                                                </select>
                                            </div>

                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status">Select Status</label>
                                                <!-- <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" disabled selected>Choose a status</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Overdue">Overdue</option>
                                                    <option value="Pending">Pending</option>
                                                </select> -->
                                                <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" disabled {{ old('status', $request['status'] ?? '') == '' ? 'selected' : '' }}>Choose a status</option>
                                                    <option value="Completed" {{ old('status', $request['status'] ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Overdue" {{ old('status', $request['status'] ?? '') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="Pending" {{ old('status', $request['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <!-- <input type="date" class="form-control" name="from_date" id="from_date"> -->
                                                <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                                            </div>

                                            <!-- To Date Filter -->
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <!-- <input type="date" class="form-control" name="to_date" id="to_date"> -->
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
                                        </div>

                                        <div class="form-row mt-3">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Report Section -->
                            <section id="reports-section">
                                @if ($counts['completed'] != 0 || $counts['overdue'] != 0 || $counts['pending'] != 0 || $assignments_percentages['completed'] || $assignments_percentages['overdue'] || $assignments_percentages['pending'] )
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="containerchart" style="display: flex;align-items: center;justify-content: center;">
                                            <div>
                                                <canvas id="completionpieChart" width="600" height="600"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class="mb-4">Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card text-white bg-success mb-3">
                                                    <div class="card-header">Completed</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['completed'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['completed'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-white bg-danger mb-3">
                                                    <div class="card-header">Overdue</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['overdue'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['overdue'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-white bg-warning mb-3">
                                                    <div class="card-header">Pending</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['pending'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['pending'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Group Name</th>
                                                    <th>Test Name</th>
                                                    <th>Start Date</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tests as $test)
                                                <tr>
                                                    <td>{{App\Models\User::find($test->student_id)->name}}</td>
                                                    <td>{{App\Models\Group::find(App\Models\GroupStudent::where('student_id',$test->student_id )->pluck('group_id'))->first()->name}}</td>
                                                    <td>{{ $test->tests->name }}</td>
                                                    <td>{{ $test->start_date }}</td>
                                                    <td>{{ $test->due_date }}</td>
                                                    <td>{{ $test->status == 1 ? 'Completed' : (\Carbon\Carbon::parse($test->due_date)->isPast() ? 'Overdue' : 'Pending') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>

                            <!-- <div style="margin-top: 20px; margin-left:10px;">
                                <p>No data available for the selected filters.</p>
                            </div> -->
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
        <!-- Footer -->
    </div>
</div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($counts))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var data = @json($counts);
        const labels = Object.keys(data);
        const values = Object.values(data);
        var ctx = document.getElementById("completionpieChart").getContext("2d");

        // Destroy previous chart instance if it exists
        if (window.completionChart) {
            window.completionChart.destroy();
        }
        window.completionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Count',
                    data: values,
                    backgroundColor: ['#1cd0a0', '#d84d42', '#e3b00d'],
                    borderColor: ['#1cd0a0', '#d84d42', '#e3b00d'],
                    borderWidth: 1,
                    barThickness: 100
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                },
                layout: {
                    padding: {
                        left: 50,
                        right: 50
                    }
                }
            }
        });
    });
</script>
@endif
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
        title: @json(session('error')),

        icon: 'error',
        confirmButtonText: 'Ok'
    });
    var canvas = document.getElementById("reports-section");
    canvas.style.display = 'none';
</script>
@endif

<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        var selectedProgramId = "{{ $request['program_id'] ?? '' }}";
        var selectedTeacherId = "{{ $request['teacher_id'] ?? '' }}";
        var selectedAssignmentId = "{{ $request['assignment_id'] ?? '' }}";

        $('#sch_id').change(function() {
            var schoolId = $('#sch_id option:selected').data('school');
            getSchoolTeachers(schoolId, selectedTeacherId);
            getProgramsBySchool(schoolId, selectedProgramId);
        });

        $('#teacher_id').change(function() {
            var teacherId = $('#teacher_id option:selected').val();
            getAssignmentsByTeacher(teacherId, selectedAssignmentId);
        });

        $('#sch_id').trigger('change');
    });

    function getProgramsBySchool(schoolId, selectedProgramId) {
        $.ajax({
            url: '/get-programs-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="program_id"]').empty();

                // Append the "Choose a Program" option
                $('select[name="program_id"]').append('<option value="">Choose a Program</option>');

                // Append the fetched program options
                $.each(data, function(key, value) {
                    $('select[name="program_id"]').append('<option value="' +
                        value.id + '">' + value.program_details + '</option>');
                });

                // Re-select the program_id if it exists
                if (selectedProgramId) {
                    $('select[name="program_id"]').val(selectedProgramId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching programs:', error);
            }
        });
    }

    function getSchoolTeachers(schoolId, selectedTeacherId) {
        $.ajax({
            url: '/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="teacher_id"]').empty();
                $('select[name="teacher_id"]').append('<option value="" selected>Choose a Teacher</option>');

                $.each(data, function(key, value) {
                    $('select[name="teacher_id"]').append('<option value="' +
                        value.id + '">' + value.name + '</option>');
                });

                // Re-select the teacher_id if it exists
                if (selectedTeacherId) {
                    $('select[name="teacher_id"]').val(selectedTeacherId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teachers:', error);
            }
        });
    }

    function getAssignmentsByTeacher(teacherId, selectedAssignmentId) {
        $.ajax({
            url: '/get-teacher-assignments/' + teacherId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="assignment_id"]').empty();
                $('select[name="assignment_id"]').append('<option value="" selected >All Assignments</option>');

                $.each(data, function(key, value) {
                    $('select[name="assignment_id"]').append('<option value="' +
                        value.id + '">' + value.name + '</option>');
                });

                if (selectedAssignmentId) {
                    $('select[name="assignment_id"]').val(selectedAssignmentId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endsection