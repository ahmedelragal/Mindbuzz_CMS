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
                                    <h5 class="title">Student Completion Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.completionReport') }}" id="page-form">
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
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id" id="student_id">
                                                    <option value="" selected disabled>No Available Students</option>
                                                </select>
                                            </div>
                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" disabled selected>No Available Programs</option>
                                                </select>
                                            </div>

                                            @role('school')
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
                                            @endrole
                                        </div>
                                        <div class="row mt-4">
                                            @role('Admin')
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
                                            @endrole
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
                                        <!-- Submit Button -->
                                        <div class="col-md-4 mt-3">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Report Section -->
                            <section id="reports-section">
                                @if (isset($counts))
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
                                                    <?php
                                                    $groupId = App\Models\GroupStudent::where('student_id', $test->student_id)->value('group_id');
                                                    $groupName = $groupId ? App\Models\Group::find($groupId)?->name : null;

                                                    if ($groupName) {
                                                        echo '<td>' . $groupName . '</td>';
                                                    } else {
                                                        echo '<td>No Group Found</td>';
                                                    }
                                                    ?>
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            @include('dashboard.layouts.footer')
        </div>
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
        title: @json($error),
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

        // Get previously selected program_id from localStorage if exists
        var selectedProgramId = "{{$request['program_id'] ?? '' }}";
        var selectedStudentId = "{{$request['student_id'] ?? '' }}";

        // Trigger getProgramsByGroup on group change
        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getSchoolStudents(schoolId, selectedStudentId);
        });
        $('#student_id').change(function() {
            var studentId = $('#student_id').val();
            getProgramsByStudent(studentId, selectedProgramId);
        });

        // Trigger change on page load to fetch programs for the selected group
        $('#school_id').trigger('change');
        $('#student_id').trigger('change');

        // Save the selected program_id to localStorage when it changes
        $('select[name="program_id"]').change(function() {
            var programId = $(this).val();
            localStorage.setItem('selectedProgramId', programId);
        });
    });

    function getProgramsByStudent(studentId, selectedProgramId) {
        $.ajax({
            url: '/get-student-programs/' + studentId,
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
                        '<option value="" selected>All Programs</option>'
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

    function getSchoolStudents(schoolId, selectedStudentId) {
        $.ajax({
            url: '/get-students-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="student_id"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>No Available Students</option>'
                    );
                } else {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>Choose a Student</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="student_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedStudentId) {
                        $('select[name="student_id"]').val(selectedStudentId).trigger('change');
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