@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        <!-- Sidebar -->
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap">
            <!-- Main Content -->
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">Students Mastery Level Report</h3>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Section -->
                            <div class="card">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.teacherStudentsMasteryLevel') }}">
                                        <div class="row">
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
                                                    <option value="" selected disabled>Choose a teacher</option>
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

                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="Unit" selected>Unit</option>
                                                    <option value="Lesson">Lesson</option>
                                                    <option value="Game">Game</option>
                                                    <option value="Skill">Skill</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <input type="date" class="form-control" name="from_date" id="from_date">
                                            </div>

                                            <!-- To Date Filter -->
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <input type="date" class="form-control" name="to_date" id="to_date">
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
                            @if(isset($chartLabels) && isset($chartPercentage))
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

<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        $('#sch_id').change(function() {
            var schoolId = $('#sch_id option:selected').data('school');
            getSchoolTeachers(schoolId);
            getProgramsBySchool(schoolId);
        });
        $('#sch_id').trigger('change');
    });

    function getSchoolTeachers(schoolId) {
        $.ajax({
            url: '/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="teacher_id"]').empty();
                $('select[name="teacher_id"]').append(
                    '<option value="">Choose a Teacher</option>');
                $.each(data, function(key, value) {
                    $('select[name="teacher_id"]').append('<option value="' +
                        value.id + '">' +
                        value.name + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function getProgramsBySchool(schoolId) {
        $.ajax({
            url: '/get-programs-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log(data);
                $('select[name="program_id"]').empty();
                $('select[name="program_id"]').append(
                    '<option value="">Select a Program</option>');
                $.each(data, function(key, value) {
                    $('select[name="program_id"]').append('<option value="' +
                        value.id + '">' +
                        value.program_details + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endsection