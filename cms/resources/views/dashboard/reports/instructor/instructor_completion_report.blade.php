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
                                    <form method="GET" action="{{ route('reports.teacherCompletionReport') }}" id="page-form">
                                        <div class="row">
                                            <!-- School Filter -->
                                            @role('Admin')
                                            <div class="col-md-4">
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id">
                                                    <option value="" selected disabled>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endrole
                                            @role('school')
                                            <input type="hidden" name="school_id" id="school_id" value="{{ auth()->user()->school_id }}">
                                            @endrole

                                            <div class="col-md-4">
                                                <label for="teacher_id">Select Teacher</label>
                                                <select class="form-select js-select2" name="teacher_id" id="teacher_id">
                                                    <option value="" selected disabled>No Available Teachers</option>
                                                </select>
                                            </div>


                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" disabled selected>No Available Programs</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <label for="assignment_id">Select Assignment</label>
                                                <select class="form-select js-select2" name="assignment_id" id="assignment_id">
                                                    <option value="" disabled selected>No Available Assignments</option>
                                                </select>
                                            </div>

                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status">Select Status</label>
                                                <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" selected {{ old('status', $request['status'] ?? '') == '' ? 'selected' : '' }}>All Status</option>
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
                                                <div class="card text-white mb-3">
                                                    <div class="card-header">Completed</div>
                                                    <div class="card-body" style="background-color: #d17e00a8;">
                                                        <h5 class="card-title">{{ $counts['completed'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['completed'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="card text-white mb-3">
                                                    <div class="card-header bg-warning">Pending</div>
                                                    <div class=" card-body" style="background-color: #f4bd0ea3;">
                                                        <h5 class="card-title">{{ $counts['pending'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['pending'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="card text-white mb-3">
                                                    <div class="card-header" style="background-color: #ff0000cf;">Overdue</div>
                                                    <div class="card-body bg-danger">
                                                        <h5 class="card-title">{{ $counts['overdue'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['overdue'] }}%</p>
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
        const labels = Object.keys(data).map(label => label.charAt(0).toUpperCase() + label.slice(1));
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
                    backgroundColor: ['#D17E00', '#ff0000cf', '#f4bd0e'],
                    borderColor: ['#D17E00', '#ff0000cf', '#f4bd0e'],
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
                },
                onClick: function(evt, item) {
                    if (item.length > 0) {
                        const index = item[0].index;
                        const clickedLabel = labels[index];
                        let filterValue;
                        if (clickedLabel === 'Completed') {
                            filterValue = 'Completed';
                        } else if (clickedLabel === 'Overdue') {
                            filterValue = 'Overdue';
                        } else if (clickedLabel === 'Pending') {
                            filterValue = 'Pending';
                        }
                        if (filterValue) {
                            document.getElementById('status').value = filterValue;
                            document.getElementById('page-form').submit();
                        }
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

@if(isset($error))
<script>
    Swal.fire({
        title: 'Error!',
        text: @json($error),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
    document.getElementById('report_container').style.display = 'none';
</script>
@endif

<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        var selectedProgramId = "{{ $request['program_id'] ?? '' }}";
        var selectedTeacherId = "{{ $request['teacher_id'] ?? '' }}";
        var selectedAssignmentId = "{{ $request['assignment_id'] ?? '' }}";

        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getSchoolTeachers(schoolId, selectedTeacherId);
        });

        $('#teacher_id').change(function() {
            var teacherId = $('#teacher_id option:selected').val();
            getProgramsByTeacher(teacherId, selectedProgramId);
            getAssignmentsByTeacher(teacherId, selectedAssignmentId);
        });

        $('#school_id').trigger('change');
        $('#teacher_id').trigger('change');
    });

    function getSchoolTeachers(schoolId, selectedTeacherId) {
        $.ajax({
            url: '/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {

                // Clear the existing options
                $('select[name="teacher_id"]').empty();

                if (!data || data.length === 0) {
                    $('select[name="teacher_id"]').append(
                        '<option value="" selected disabled>No Available Teachers</option>'
                    );
                } else {

                    $('select[name="teacher_id"]').append(
                        '<option value="" selected disabled>Choose a Teacher</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="teacher_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });


                    if (selectedTeacherId) {
                        console.log('asasa');
                        $('select[name="teacher_id"]').val(selectedTeacherId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
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
                if (!data || data.length === 0) {
                    $('select[name="assignment_id"]').append(
                        '<option value="" selected disabled>No Available Assignments for this Teacher</option>'
                    );
                } else {
                    $('select[name="assignment_id"]').append('<option value="" selected >All Assignments</option>');
                    $.each(data, function(key, value) {
                        $('select[name="assignment_id"]').append('<option value="' +
                            value.id + '">' + value.name + '</option>');
                    });

                    if (selectedAssignmentId) {
                        $('select[name="assignment_id"]').val(selectedAssignmentId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function getProgramsByTeacher(teacherId, selectedProgramId) {
        $.ajax({
            url: '/get-teacher-programs/' + teacherId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="program_id"]').empty();

                if (!data || data.length === 0) {
                    $('select[name="program_id"]').append(
                        '<option value="" selected disabled>No Available Programs</option>'
                    );
                } else {

                    $('select[name="program_id"]').append(
                        '<option value="" selected disabled>All Programs</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="program_id"]').append(
                            '<option value="' + value.id + '">' + value.program_details + '</option>'
                        );
                    });


                    if (selectedProgramId) {
                        $('select[name="program_id"]').val(selectedProgramId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endsection