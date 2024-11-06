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
                            <!-- Form Section -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="title">Teacher's Students Mastery Level Report</h5>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.teacherStudentsMasteryLevel') }}">
                                        <div class="row">
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
                                                <select class="form-select js-select2" name="program_id" id="program_id" required>
                                                    <option value="" disabled selected>No Available Programs</option>
                                                </select>
                                            </div>

                                            @role('school')
                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter" required>
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
                                                </select>
                                            </div>
                                            @endrole
                                        </div>

                                        <div class="row mt-3">
                                            @role('Admin')
                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter" required>
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
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
                                        <div class="col-md-4 mt-4">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Report Section -->
                            @if(isset($chartLabels) && isset($chartPercentage))
                            <div class="card mt-4">
                                <div class="card-body">
                                    <!-- Display Chart if Data is Available -->
                                    <div class="container mt-5">
                                        <canvas id="masteryChart" width="400" height="200"></canvas>
                                    </div>

                                    <!-- Display Chart if Data is Available -->
                                    <div class="container mt-5">
                                        <canvas id="usageChart" width="400" height="200" style="display:none;"></canvas>
                                    </div>
                                    <!-- <div class="container mt-5">
                                        <canvas id="masteryChartNumbers" width="400" height="200" style="display:none;"></canvas>
                                    </div> -->
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-body">
                                    @if (!empty($units) || !empty($lessons) || !empty($games) || !empty($skills))

                                    @if (!empty($units))
                                    <h5>Units Mastery</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Mastery Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($units as $unit)
                                            <tr>
                                                <td>{{ $unit['name'] }}</td>
                                                <td>{{ $unit['failed'] }}</td>
                                                <td>{{ $unit['introduced'] }}</td>
                                                <td>{{ $unit['practiced'] }}</td>
                                                <td>{{ $unit['mastered'] }}</td>
                                                <td>{{ $unit['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($lessons))
                                    <h5>Lessons Mastery</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Lesson</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Mastery Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lessons as $lesson)
                                            <tr>
                                                <td>{{ $lesson['name'] }}</td>
                                                <td>{{ $lesson['failed'] }}</td>
                                                <td>{{ $lesson['introduced'] }}</td>
                                                <td>{{ $lesson['practiced'] }}</td>
                                                <td>{{ $lesson['mastered'] }}</td>
                                                <td>{{ $lesson['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($games))
                                    <h5>Games Mastery</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Game</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Mastery Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($games as $game)
                                            <tr>
                                                <td>{{ $game['name'] }}</td>
                                                <td>{{ $game['failed'] }}</td>
                                                <td>{{ $game['introduced'] }}</td>
                                                <td>{{ $game['practiced'] }}</td>
                                                <td>{{ $game['mastered'] }}</td>
                                                <td>{{ $game['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($skills))
                                    <h5>Skills Mastery</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Skill</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Mastery Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skills as $skill)
                                            <tr>
                                                <td>{{ $skill['name'] }}</td>
                                                <td>{{ $skill['failed'] }}</td>
                                                <td>{{ $skill['introduced'] }}</td>
                                                <td>{{ $skill['practiced'] }}</td>
                                                <td>{{ $skill['mastered'] }}</td>
                                                <td>{{ $skill['mastery_percentage'] }}%</td>
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
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection




<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($chartLabels) && isset($chartPercentage))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from your controller
        var chartLabels = @json($chartLabels);
        var chartPercentage = @json($chartPercentage);
        console.log(chartPercentage);
        // Create the bar chart
        var ctx = document.getElementById('masteryChart').getContext('2d');
        var loginChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: 'Mastery Percentage',
                    data: chartPercentage,
                    backgroundColor: '#E9C874',
                    borderColor: '#E9C874',
                    borderWidth: 1,
                    barThickness: 100
                }]
            },
            options: {
                scales: {
                    x: {
                        min: 0,
                        max: chartLabels.length > 1 ? chartLabels.length - 1 : 1,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        max: 100, // Ensure the y-axis always goes up to 100
                        ticks: {
                            callback: function(value) {
                                return value + '%'; // Add '%' to the y-axis labels
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.raw + '%'; // Format tooltip values as percentages
                            }
                        }
                    }
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

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

        var selectedProgramId = "{{$request['program_id'] ?? '' }}";
        var selectedTeacherId = "{{ $request['teacher_id']?? '' }}";

        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getSchoolTeachers(schoolId, selectedTeacherId);
        });

        $('#teacher_id').change(function() {
            var teacherId = $('#teacher_id option:selected').val();
            getProgramsByTeacher(teacherId, selectedProgramId);
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
                        '<option value="" selected disabled>Choose a Program</option>'
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