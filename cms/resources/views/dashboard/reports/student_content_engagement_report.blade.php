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
                                    <h5 class="title">Student Content Engagement Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.studentContentEngagementReport') }}">
                                        <div class="row">
                                            <!-- School Filter -->
                                            @role('Admin')
                                            <div class="col-md-4">
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id" required>
                                                    <option value="" disabled {{ old('school_id', $request['school_id'] ?? '') == '' ? 'selected' : '' }}>Choose a school/class</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endrole
                                            @role('school')
                                            <input type="hidden" name="school_id" value="{{ auth()->user()->school_id }}">
                                            @endrole


                                            <div class="col-md-4">
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id" id="student_id">
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Student</option>
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schStudents = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 2)
                                                    ->where('is_student', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schStudents as $student)
                                                    <option value="{{ $student->id }}" {{ old('student_id', $request['student_id'] ?? '') == $student->id ? 'selected' : '' }}>
                                                        {{ $student->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id" required>
                                                    <option value="" disabled selected>Choose a Program</option>
                                                    @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}">
                                                        {{ $program->course ? $program->course->name : 'No Course' }} /
                                                        {{ $program->stage ? $program->stage->name : 'No Stage' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="Unit" selected{{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
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

                                            <!-- Submit Button -->
                                            <div class="col-md-4 mt-4">
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Report Section -->
                            @if(isset($highEngagementLabels) || isset($highEngagementValues) || isset($lowEngagementLabels) || isset($lowEngagementValues))
                            <div class="card mt-4">
                                <div class="card-body">
                                    <!-- Display Chart if Data is Available -->
                                    <div class="container mt-5">
                                        <canvas id="masteryChart" width="400" height="200"></canvas>
                                    </div>
                                    <div class="container mt-5">
                                        <canvas id="masteryChartNumbers" width="400" height="200" style="display:none;"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-body">
                                    @if (isset($unitsEngagement) || isset($lessonsEngagement) || isset($gameEngagement) || isset($skillsEngagement))

                                    @if (isset($unitsEngagement))
                                    <h5>Units Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($unitsEngagement as $engagement)
                                            <tr>
                                                <td>{{ $engagement['name'] }}</td>
                                                <td>{{ $engagement['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($lessonsEngagement))
                                    <h5>Lessons Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Lesson</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lessonsEngagement as $engagement)
                                            <tr>
                                                <td>{{ $engagement['name'] }}</td>
                                                <td>{{ $engagement['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($gameEngagement))
                                    <h5>Game Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Game</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($gameEngagement as $engagement)
                                            <tr>
                                                <td>{{ $engagement['name'] }}</td>
                                                <td>{{ $engagement['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($skillsEngagement))
                                    <h5>Skills Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Skill</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skillsEngagement as $engagement)
                                            <tr>
                                                <td>{{ $engagement['name'] }}</td>
                                                <td>{{ $engagement['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @else
                                    <p>No data available for the selected filters.</p>
                                    @endif
                                </div>
                            </div>
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
<script>
    $(document).ready(function() {
        // Initialize select2 for the filters
        $('.js-select2').select2();

        // Retrieve the PHP variable in JavaScript
        var selectedStudentId = "{{ $request['student_id'] ?? '' }}";
        var selectedProgramId = "{{ $request['program_id'] ?? '' }}";

        // Trigger change on page load
        var schoolId = $('#school_id option:selected').data('school');
        if (schoolId) {
            getSchoolStudents(schoolId, function() {
                setSelectedStudent(selectedStudentId);
                setSelectedProgram(selectedProgramId);
            });
        }

        // Fetch students when school is changed
        $('#school_id').change(function() {
            var schoolId = $('#school_id option:selected').data('school');
            getSchoolStudents(schoolId, function() {
                setSelectedStudent(selectedStudentId);
            });
        });
    });

    function getSchoolStudents(schoolId, callback) {
        $.ajax({
            url: '/get-students-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="student_id"]').empty();
                $('select[name="student_id"]').append('<option value="">Choose a Student</option>');
                $.each(data, function(key, value) {
                    $('select[name="student_id"]').append('<option value="' +
                        value.id + '">' + value.name + '</option>');
                });

                // Re-initialize select2 to refresh the options
                $('select[name="student_id"]').select2();

                // Execute the callback function if provided
                if (typeof callback === 'function') {
                    callback();
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function setSelectedStudent(studentId) {
        if (studentId) {
            // Check if the student ID exists in the options
            if ($('select[name="student_id"] option[value="' + studentId + '"]').length > 0) {
                $('select[name="student_id"]').val(studentId).trigger('change');
            }
        }
    }

    function setSelectedProgram(ProgramId) {
        if (ProgramId) {
            // Check if the student ID exists in the options
            if ($('select[name="program_id"] option[value="' + ProgramId + '"]').length > 0) {
                $('select[name="program_id"]').val(ProgramId).trigger('change');
            }
        }
    }
</script>

@if (isset($highEngagementLabels) || isset($highEngagementValues) || isset($lowEngagementLabels) || isset($lowEngagementValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from your controller
        var highEngagementLabels = @json($highEngagementLabels);
        var highEngagementValues = @json($highEngagementValues);
        var lowEngagementLabels = @json($lowEngagementLabels);
        var lowEngagementValues = @json($lowEngagementValues);
        // Create the bar chart
        var ctx = document.getElementById('masteryChart').getContext('2d');


        function getRandomColor() {
            // Decide whether to generate a blue or gray shade
            var isBlue = Math.random() < 0.5; // 50% chance to pick blue or gray

            if (isBlue) {
                // Blue shades
                var hue = Math.floor(Math.random() * 21) + 200; // Random hue between 200 and 220 for blue tones
                var saturation = Math.floor(Math.random() * 22) + 60; // Saturation between 60% and 80% for pastel effect
                var lightness = Math.floor(Math.random() * 22) + 60; // Lightness between 60% and 80% for pastel shades
                return `hsl(${hue}, ${saturation}%, ${lightness}%)`; // HSL format for pastel blue colors
            } else {
                // Gray shades
                var lightness = Math.floor(Math.random() * 41) + 40; // Lightness between 40% and 80% for gray shades
                return `hsl(0, 0%, ${lightness}%)`; // HSL format for gray colors
            }
        }




        var datasets = [];

        // Low Engagement datasets
        for (var i = 0; i < lowEngagementLabels.length; i++) {
            datasets.push({
                label: lowEngagementLabels[i] + ' (' + lowEngagementValues[i] + '%)',
                data: [lowEngagementValues[i], 0],
                backgroundColor: getRandomColor()
            });
        }

        // High Engagement datasets
        for (var i = 0; i < highEngagementLabels.length; i++) {
            datasets.push({
                label: highEngagementLabels[i] + ' (' + highEngagementValues[i] + '%)',
                data: [0, highEngagementValues[i]],
                backgroundColor: getRandomColor()
            });
        }

        // Create the chart
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Low Engagement', 'High Engagement'],
                datasets: datasets
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Engagements - Low vs High'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        beginAtZero: true,
                        stacked: true
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
        title: 'Error!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif
@endsection