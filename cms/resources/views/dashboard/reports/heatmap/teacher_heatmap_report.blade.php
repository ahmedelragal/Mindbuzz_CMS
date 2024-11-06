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
                                    <h5 class="title">Teacher Heatmap Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.teacherHeatmapReport') }}">
                                        <div class="row">
                                            <!-- School Filter -->
                                            @role('Admin')
                                            <div class="col-md-4">
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id" required>
                                                    <option value="" disabled {{ old('school_id', $request['school_id'] ?? '') == '' ? 'selected' : '' }}>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @endrole
                                            @role('school')
                                            <input type="hidden" name="school_id" value="{{Auth::user()->school_id}}">
                                            @endrole
                                            <!-- Teacher Filter -->
                                            <div class="col-md-4">
                                                <label for="teacher1_id">Select First Teacher</label>
                                                <select class="form-select js-select2" name="teacher1_id" id="teacher1_id">
                                                    <option value="" selected disabled>Choose a Teacher</option>
                                                    @role('school')
                                                    @php
                                                    $schTeachers = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schTeachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ old('teacher1_id', $request['teacher1_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>
                                            <!-- Teacher Filter -->
                                            <div class="col-md-4">
                                                <label for="teacher2_id">Select Second Teacher</label>
                                                <select class="form-select js-select2" name="teacher2_id" id="teacher2_id">
                                                    <option value="" selected disabled>Choose a Teacher</option>
                                                    @role('school')
                                                    @php
                                                    $schTeachers = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schTeachers as $teacher)
                                                    <option value="{{ $teacher->id }}" {{ old('teacher2_id', $request['teacher2_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                                                        {{ $teacher->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>
                                            @role('school')
                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" selected disabled>No Available Programs</option>
                                                </select>
                                            </div>
                                            @endrole
                                        </div>

                                        <div class="row mt-3">
                                            @role('Admin')
                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" selected disabled>No Available Programs</option>
                                                </select>
                                            </div>
                                            @endrole
                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="" selected>No Filter</option>
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <!-- <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>  -->
                                                </select>
                                            </div>
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <!-- <input type="date" class="form-control" name="from_date" id="from_date"> -->
                                                <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                                            </div>

                                            <!-- To Date Filter -->
                                            @role('school')
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <!-- <input type="date" class="form-control" name="to_date" id="to_date"> -->
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
                                            @endrole
                                            @role('Admin')
                                            <div class="col-md-4 mt-3">
                                                <label for="to_date">To Date</label>
                                                <!-- <input type="date" class="form-control" name="to_date" id="to_date"> -->
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
                                            @endrole

                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-md-4 mt-4">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Report Section -->
                            <div id="report_container" style="display:none">

                                <div class="card mt-4">
                                    <div class="card-body">
                                        @if (isset($programsUsage) || isset($unitsUsage) || isset($lessonsUsage) || isset($gamesUsage) || isset($skillsUsage))
                                        <!-- Display Chart if Data is Available -->
                                        <div class="row">
                                            <div class="container mt-5">
                                                <canvas id="usageChart" width="400" height="200"></canvas>
                                            </div>
                                            <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                                <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous Unit</button>
                                                <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next Unit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="card mt-4">
                                    <div class="card-body">

                                        @if (isset($programsUsage))
                                        <h5>Programs Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Program</th>
                                                    <th>{{$teacherName1}} Usage(%)</th>
                                                    <th>{{$teacherName2}} Usage(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($programsUsage as $program)
                                                <tr>
                                                    <td>{{ $program['name'] }}</td>
                                                    <td>{{ $program['usage_percentage'] }}%</td>
                                                    <td>{{ $programsUsage2[$program['program_id']]['usage_percentage'] }}%</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($unitsUsage))
                                        <h5>Units Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>{{$teacherName1}} Usage(%)</th>
                                                    <th>{{$teacherName2}} Usage(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unitsUsage as $unit)
                                                <tr>
                                                    <td>{{ $unit['name'] }}</td>
                                                    <td>{{ $unit['usage_percentage'] }}%</td>
                                                    <td>{{ $unitsUsage2[$unit['unit_id']]['usage_percentage'] }}%</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif



                                        @if (isset($lessonsUsage))
                                        <h5>Lessons Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>lesson</th>
                                                    <th>{{$teacherName1}} Usage(%)</th>
                                                    <th>{{$teacherName2}} Usage(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lessonsUsage as $unit)
                                                <tr>
                                                    <td>{{$unit['name']}}</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                @foreach ($unit['lessons'] as $lesson)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td> <?php echo $lesson['usage_percentage'] ?>%</td>
                                                    <td> <?php echo $lessonsUsage2[$unit['unit_id']]['lessons'][$lesson['lesson_id']]['usage_percentage'] ?>%</td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($gamesUsage))
                                        <h5>Games Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Lesson</th>
                                                    <th>Game</th>
                                                    <th>{{$teacherName1}} Status</th>
                                                    <th>{{$teacherName2}} Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($gamesUsage as $unit)
                                                @foreach ($unit['lessons'] as $lesson)

                                                <tr>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                @foreach ($lesson['games'] as $game)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$game['name']}}</td>
                                                    <td> <?php echo $game['assigned'] == 1 ? 'Assigned' : 'Unassigned'; ?></td>
                                                    <td> <?php echo $gamesUsage2[$unit['unit_id']]['lessons'][$lesson['lesson_id']]['games'][$game['game_type_id']]['assigned'] == 1 ? 'Assigned' : 'Unassigned'; ?></td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($skillsUsage))

                                        <h5>Skill Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Lesson/Game</th>
                                                    <th>Skill</th>
                                                    <th>Usage Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($skillsUsage as $unit)
                                                @foreach ($unit['lessons'] as $lesson)
                                                @foreach ($lesson['games'] as $game)
                                                <tr>
                                                    <td>{{$unit['name']}}</td>
                                                    <td>{{$lesson['name']}} / {{$game['name']}}</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>

                                                @foreach ($game['skills'] as $skill)
                                                <tr>
                                                    <td></td>
                                                    <td></td>
                                                    <td>{{ $skill['name'] != null ? $skill['name'] : 'No skill specified' }}</td>
                                                    <td>{{ $skill['usage_count'] != null ? $skill['usage_count'] : 0 }}</td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

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
@if (isset($gamesLabels) || isset($gamesValues) || isset($gamesLabels2) || isset($gamesValues2))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller for the two charts
        const gamesLabels = @json($gamesLabels);
        const gamesValues = @json($gamesValues);
        const gamesLabels2 = @json($gamesLabels2);
        const gamesValues2 = @json($gamesValues2);
        const teacherName1 = @json($teacherName1);
        const teacherName2 = @json($teacherName2);

        // Function to group lessons by unit using the "-" separator
        function groupByUnit(names, usageCounts) {
            const units = [];
            let currentUnit = [];
            names.forEach((label, index) => {
                if (label !== "-") {
                    currentUnit.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentUnit.length > 0) {
                    units.push(currentUnit);
                    currentUnit = [];
                }
            });
            if (currentUnit.length > 0) {
                units.push(currentUnit);
            }
            return units;
        }

        // Group units for both charts
        const units1 = groupByUnit(gamesLabels, gamesValues);
        const units2 = groupByUnit(gamesLabels2, gamesValues2);

        // Initialize dynamic pagination variables
        let currentPage = 0;

        // Initialize both charts
        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';

        toggleButtons();


        // Initialize the chart with the first page data
        let usageChart = initializeChart(
            ctx,
            units1[currentPage].map(item => item.label),
            units1[currentPage].map(item => item.value),
            units2[currentPage].map(item => item.value)
        );

        // Function to initialize the chart with grouped bars
        function initializeChart(ctx, labels, data1, data2) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: teacherName1,
                            data: data1,
                            backgroundColor: '#E9C874',
                            borderColor: '#E9C874',
                            borderWidth: 1,
                            maxBarThickness: 80
                        },
                        {
                            label: teacherName2,
                            data: data2,
                            backgroundColor: '#74B9E9',
                            borderColor: '#74B9E9',
                            borderWidth: 1,
                            maxBarThickness: 80
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            ticks: {
                                stepSize: 1,
                            },
                            beginAtZero: true,
                            max: 1,
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(tooltipItem) {
                                    let datasetLabel = tooltipItem.dataset.label;
                                    let value = tooltipItem.raw;
                                    if (value == '1') {
                                        value = "Assigned";
                                    } else {
                                        value = "Unassigned";
                                    }
                                    return `${datasetLabel}: ${value}`;
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
        }

        // Function to update the chart with new page data
        function updateChart() {
            const currentUnit1 = units1[currentPage];
            const currentUnit2 = units2[currentPage];

            usageChart.data.labels = currentUnit1.map(item => item.label);
            usageChart.data.datasets[0].data = currentUnit1.map(item => item.value);
            usageChart.data.datasets[1].data = currentUnit2.map(item => item.value);

            usageChart.update(); // Refresh the chart with new data
            toggleButtons();
        }

        // Optimized function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart();
            }
        };

        // Optimized function to go to the next page
        window.nextPage = function() {
            if (currentPage < units1.length - 1) {
                currentPage++;
                updateChart();
            }
        };

        // Function to toggle button visibility based on the current page
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            prevButton.style.display = currentPage === 0 ? 'none' : 'block';
            nextButton.style.display = currentPage === units1.length - 1 ? 'none' : 'block';
        }
    });
</script>
@endif

@if (isset($chartLabels) || isset($chartValues) || isset($chartLabels2) || isset($chartValues2))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from the controller for the two charts
        const names1 = @json($chartLabels);
        const usageCounts1 = @json($chartValues);
        const names2 = @json($chartLabels2);
        const usageCounts2 = @json($chartValues2);
        const teacherName1 = @json($teacherName1);
        const teacherName2 = @json($teacherName2);


        // Function to group lessons by unit using the "-" separator
        function groupByUnit(names, usageCounts) {
            const units = [];
            let currentUnit = [];
            names.forEach((label, index) => {
                if (label !== "-") {
                    currentUnit.push({
                        label: label,
                        value: usageCounts[index]
                    });
                } else if (currentUnit.length > 0) {
                    units.push(currentUnit);
                    currentUnit = [];
                }
            });
            if (currentUnit.length > 0) {
                units.push(currentUnit);
            }
            return units;
        }

        // Group lessons by units for both datasets
        const units1 = groupByUnit(names1, usageCounts1);
        const units2 = groupByUnit(names2, usageCounts2);

        // Initialize dynamic pagination variables
        let currentPage = 0;

        // Initialize the chart context
        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';

        toggleButtons();

        // Initialize the chart with the first page data
        let usageChart = initializeChart(
            ctx,
            units1[currentPage].map(item => item.label),
            units1[currentPage].map(item => item.value),
            units2[currentPage].map(item => item.value)
        );

        // Function to initialize the chart with grouped bars
        function initializeChart(ctx, labels, data1, data2) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                            label: teacherName1,
                            data: data1,
                            backgroundColor: '#E9C874',
                            borderColor: '#E9C874',
                            borderWidth: 1,
                            maxBarThickness: 80
                        },
                        {
                            label: teacherName2,
                            data: data2,
                            backgroundColor: '#74B9E9',
                            borderColor: '#74B9E9',
                            borderWidth: 1,
                            maxBarThickness: 80
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            stacked: false,
                            barPercentage: 1.0,
                            categoryPercentage: 1.0
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                },
                                stepSize: 10
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(tooltipItem) {
                                    let datasetLabel = tooltipItem.dataset.label;
                                    let value = tooltipItem.raw; // Bar value
                                    return `${datasetLabel}: ${value}%`;
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
        }

        // Function to update the chart with new page data
        function updateChart() {
            const currentUnit1 = units1[currentPage];
            const currentUnit2 = units2[currentPage];

            usageChart.data.labels = currentUnit1.map(item => item.label);
            usageChart.data.datasets[0].data = currentUnit1.map(item => item.value);
            usageChart.data.datasets[1].data = currentUnit2.map(item => item.value);

            usageChart.update(); // Refresh the chart with new data
            toggleButtons();
        }

        // Optimized function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart();
            }
        };

        // Optimized function to go to the next page
        window.nextPage = function() {
            if (currentPage < units1.length - 1) {
                currentPage++;
                updateChart();
            }
        };

        // Function to toggle button visibility based on the current page
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            prevButton.style.display = currentPage === 0 ? 'none' : 'block';
            nextButton.style.display = currentPage === units1.length - 1 ? 'none' : 'block';
        }
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

@php
if (isset($programsUsage) || isset($unitsUsage) || isset($lessonsUsage) || isset($gamesUsage) || isset($skillsUsage)){
$showReports = 1;
}else{
$showReports = 0;
}
@endphp

<script>
    $(document).ready(function() {
        $('.js-select2').select2();
        showReports = @json($showReports);
        if (showReports) {
            document.getElementById('report_container').style.display = 'block';
        }
        // Get previously selected program_id from if exists
        var selectedProgramId = "{{$request['program_id'] ?? '' }}";
        var selectedTeacherId = "{{ $request['teacher1_id']?? '' }}";
        var selectedTeacherId2 = "{{ $request['teacher2_id']?? '' }}";

        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getSchoolTeachers(schoolId, selectedTeacherId, selectedTeacherId2)
        });

        // Trigger getCommonTeachersPrograms on group change
        $('#teacher1_id').change(function() {
            var teacher1Id = $('#teacher1_id').val();
            var teacher2Id = $('#teacher2_id').val();
            getCommonTeachersPrograms(teacher1Id, teacher2Id, selectedProgramId);
        });
        $('#teacher2_id').change(function() {
            var teacher1Id = $('#teacher1_id').val();
            var teacher2Id = $('#teacher2_id').val();
            getCommonTeachersPrograms(teacher1Id, teacher2Id, selectedProgramId);
        });

        // Trigger change on page load to fetch programs for the selected group
        $('#school_id').trigger('change');
        $('#teacher2_id').trigger('change');

        // Save the selected program_id to localStorage when it changes
        $('select[name="program_id"]').change(function() {
            var programId = $(this).val();
            localStorage.setItem('selectedProgramId', programId);
        });
    });

    function getCommonTeachersPrograms(teacher1Id, teacher2Id, selectedProgramId) {
        $.ajax({
            url: '/get-common-programs-teacher/' + teacher1Id + '/' + teacher2Id,
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

    function getSchoolTeachers(schoolId, selectedTeacherId, selectedTeacherId2) {
        $.ajax({
            url: '/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="teacher1_id"]').empty();
                $('select[name="teacher2_id"]').empty();
                $('select[name="teacher1_id"]').append('<option value="" selected disabled>Choose a Teacher</option>');
                $('select[name="teacher2_id"]').append('<option value="" selected disabled>Choose a Teacher</option>');

                $.each(data, function(key, value) {
                    $('select[name="teacher1_id"]').append('<option value="' +
                        value.id + '">' + value.name + '</option>');
                    $('select[name="teacher2_id"]').append('<option value="' +
                        value.id + '">' + value.name + '</option>');
                });

                // Re-select the teacher_id if it exists
                if (selectedTeacherId) {
                    $('select[name="teacher1_id"]').val(selectedTeacherId).trigger('change');
                }
                if (selectedTeacherId2) {
                    $('select[name="teacher2_id"]').val(selectedTeacherId2).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teachers:', error);
            }
        });
    }
</script>

@endsection