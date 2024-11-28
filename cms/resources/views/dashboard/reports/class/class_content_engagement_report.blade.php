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
                                    <h5 class="title">Class Content Engagement Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classContentEngagementReport') }}">
                                        <div class="row">
                                            <!-- Group Filter -->
                                            <div class="col-md-4">
                                                @role('Admin')
                                                <label for="group_id">Select school/class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled {{ old('group_id', $request['group_id'] ?? '') == '' ? 'selected' : '' }}>Choose a school/class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <!-- <option value="{{ $group->id }}" data-school="{{ $sch->id }}">{{ $sch->name }} / {{ $group->name }}</option> -->
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}" {{ old('group_id', $request['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                        {{ $sch->name }} / {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole
                                                @role('school')
                                                <label for="group_id">Select Class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled {{ old('group_id', $request['group_id'] ?? '') == '' ? 'selected' : '' }}>Choose a Class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <!-- <option value="{{ $group->id }}" data-school="{{ $sch->id }}">{{ $sch->name }} / {{ $group->name }}</option> -->
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}" {{ old('group_id', $request['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                        {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole
                                            </div>

                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    @role('Admin')
                                                    <option value="" selected disabled>No Available Programs</option>
                                                    @endrole
                                                    @role('school')
                                                    @if(!$programs->isEmpty())
                                                    <option value="" selected disabled>Choose a Program</option>
                                                    @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}">
                                                        {{ $program->course ? $program->course->name : 'No Course' }} /
                                                        {{ $program->stage ? $program->stage->name : 'No Stage' }}
                                                    </option>
                                                    @endforeach
                                                    @else
                                                    <option value="" selected disabled>No Available Programs</option>
                                                    @endif
                                                    @endrole
                                                </select>
                                            </div>

                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="Unit" selected {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
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
                            @if (isset($chartLabels) || isset($chartValues))
                            <div class="card mt-4">
                                <div class="card-body">
                                    <!-- Display Chart if Data is Available -->
                                    <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                        <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                        <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</span></button>
                                    </div>
                                    <div class="container mt-5">
                                        <canvas id="engagementChart" width="400" height="200"></canvas>
                                    </div>

                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-body">
                                    @if (isset($unitsEngagement) || isset($lessonsEngagement) || isset($gamesEngagement) || isset($skillsEngagement))

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
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lessonsEngagement as $unit)
                                            <?php $inc = 0; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            @if ($inc==0)
                                            <tr>
                                                <td>{{$unit['name']}}</td>
                                                <td>{{$lesson['name']}}</td>
                                                <td>{{ $lesson['engagement_percentage'] }}%</td>
                                            </tr>
                                            <?php $inc = 1; ?>
                                            @else
                                            <tr>
                                                <td></td>
                                                <td>{{$lesson['name']}}</td>
                                                <td>{{ $lesson['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endif
                                            @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($gamesEngagement))
                                    <h5>Game Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Game</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($gamesEngagement as $unit)
                                            <?php $unitPrinted = false; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            <?php $lessonPrinted = false; ?>
                                            @foreach ($lesson['games'] as $game)
                                            <?php $gamePrinted = false; ?>
                                            <tr>
                                                <!-- Print unit name only once per unit -->
                                                @if (!$unitPrinted)
                                                <td>{{ $unit['name'] }}</td>
                                                <?php $unitPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print lesson name only once per lesson -->
                                                @if (!$lessonPrinted)
                                                <td>{{ $lesson['name'] }}</td>
                                                <?php $lessonPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print game name only once per game -->
                                                @if (!$gamePrinted)
                                                <td>{{ $game['name'] }}</td>
                                                <?php $gamePrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Game details (always printed) -->
                                                <td>{{ $game['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                            @endforeach

                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (isset($skillsEngagement))
                                    <h5>Skills Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Game</th>
                                                <th>Skill</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skillsEngagement as $unit)
                                            <?php $unitPrinted = false; ?>
                                            @foreach ($unit['lessons'] as $lesson)
                                            <?php $lessonPrinted = false; ?>
                                            @foreach ($lesson['games'] as $game)
                                            <?php $gamePrinted = false; ?>
                                            @foreach ($game['skills'] as $skill)
                                            <tr>
                                                <!-- Print unit name only once per unit -->
                                                @if (!$unitPrinted)
                                                <td>{{ $unit['name'] }}</td>
                                                <?php $unitPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print lesson name only once per lesson -->
                                                @if (!$lessonPrinted)
                                                <td>{{ $lesson['name'] }}</td>
                                                <?php $lessonPrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Print game name only once per game -->
                                                @if (!$gamePrinted)
                                                <td>{{ $game['name'] }}</td>
                                                <?php $gamePrinted = true; ?>
                                                @else
                                                <td></td>
                                                @endif

                                                <!-- Skill details (always printed for each skill) -->
                                                <td>{{ $skill['name'] }}</td>
                                                <td>{{ $skill['engagement_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                            @endforeach
                                            @endforeach
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
@if (isset($chartLabels) || isset($chartValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller
        const names = @json($chartLabels);
        const usageCounts = @json($chartValues);
        const chartNames = @json($chartNames);

        // Group lessons by unit using the "-" separator
        const units = [];
        let currentUnit = [];
        names.forEach((label, index) => {
            if (label !== "-") {
                currentUnit.push({
                    label: label,
                    value: usageCounts[index],
                    chartName: chartNames[index]
                });
            } else if (currentUnit.length > 0) {
                units.push(currentUnit);
                currentUnit = [];
            }
        });
        // Push the last unit if it's not empty
        if (currentUnit.length > 0) {
            units.push(currentUnit);
        }

        // Initialize dynamic pagination variables
        let currentPage = 0;

        const ctx = document.getElementById('engagementChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';
        toggleButtons();
        // Initialize the chart with the first unit's data
        let usageChart = initializeChart(
            ctx,
            units[currentPage].map(item => item.label), // x-axis labels
            units[currentPage].map(item => item.value), // bar values
            units[currentPage].map(item => item.chartName) // game names from controller
        );

        // Function to initialize chart
        function initializeChart(ctx, labels, data, chartNames) {
            // Function to determine color based on percentage
            function getBarColor(value) {
                if (value <= 20) return '#ff3030';
                if (value > 20 && value <= 40) return '#ff6230';
                if (value > 40 && value <= 60) return '#f7d156';
                if (value > 60 && value <= 80) return '#f77556';
                return '#1cd0a0';
            }

            // Generate the backgroundColor array dynamically
            const backgroundColors = data.map(value => getBarColor(value));

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels, // x-axis labels
                    datasets: [{
                        label: 'Engagement Levels',
                        data: data, // bar values
                        chartNames: chartNames, // store the game names
                        backgroundColor: backgroundColors, // Dynamically set colors 
                        borderWidth: 1,
                        maxBarThickness: 120
                    }]
                },
                options: {
                    scales: {
                        x: {
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100, // Set the max value to 100 for percentage
                            ticks: {
                                callback: function(value) {
                                    return value + '%'; // Append '%' to each tick value
                                },
                                stepSize: 10 // Set the step size (optional)
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                generateLabels: function(chart) {
                                    return [{
                                            text: 'Low (≤ 20%)',
                                            fillStyle: '#ff3030', // Red
                                            strokeStyle: '#ff3030',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Low Average (21% - 40%)',
                                            fillStyle: '#ff6230',
                                            strokeStyle: '#ff6230',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Average (41% - 60%)',
                                            fillStyle: '#f7d156',
                                            strokeStyle: '#f7d156',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'High Average (61% - 80%)',
                                            fillStyle: '#f77556',
                                            strokeStyle: '#f77556',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'High (> 80%)',
                                            fillStyle: '#1cd0a0',
                                            strokeStyle: '#1cd0a0',
                                            lineWidth: 0
                                        }
                                    ];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    // Access the game name from chartNames
                                    const chartName = tooltipItem.dataset.chartNames[tooltipItem.dataIndex];
                                    const value = tooltipItem.raw; // Get the value (y-axis data)
                                    return `${chartName}: ${value}%`; // Show "Game Name: Value%"
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

        // Function to update the chart with the current page data (current unit)
        function updateChart() {
            const currentUnit = units[currentPage];
            if (usageChart) {
                // Update chart labels, data, and chartNames
                usageChart.data.labels = currentUnit.map(item => item.label);
                usageChart.data.datasets[0].data = currentUnit.map(item => item.value);
                usageChart.data.datasets[0].chartNames = currentUnit.map(item => item.chartName); // Update chartNames

                // Dynamically update backgroundColor based on value
                usageChart.data.datasets[0].backgroundColor = currentUnit.map(item => {
                    if (item.value <= 20) return '#ff3030';
                    if (item.value > 20 && item.value <= 40) return '#ff6230';
                    if (item.value > 40 && item.value <= 60) return '#f7d156';
                    if (item.value > 60 && item.value <= 80) return '#f77556';
                    return '#1cd0a0';
                });

                // Update the chart
                usageChart.update();
            }
            toggleButtons(); // Ensure navigation buttons are updated
        }




        // Function to go to the previous unit (previous page)
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to go to the next unit (next page)
        window.nextPage = function() {
            if (currentPage < units.length - 1) {
                currentPage++;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to toggle the visibility of the previous and next buttons
        function toggleButtons() {
            const prevButton = document.getElementById('prevBtn');
            const nextButton = document.getElementById('nextBtn');

            // If only one page, hide both buttons
            if (units.length <= 1) {
                prevButton.style.display = 'none';
                nextButton.style.display = 'none';
            } else {
                // Show or hide buttons based on the current page
                prevButton.style.display = (currentPage === 0) ? 'none' : 'block';
                nextButton.style.display = (currentPage === units.length - 1) ? 'none' : 'block';
            }
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

        // Trigger getProgramsByGroup on group change
        $('#group_id').change(function() {
            var groupId = $('#group_id').val();
            getProgramsByGroup(groupId, selectedProgramId);
        });

        // Trigger change on page load to fetch programs for the selected group
        $('#group_id').trigger('change');

        // Save the selected program_id to localStorage when it changes
        $('select[name="program_id"]').change(function() {
            var programId = $(this).val();
            localStorage.setItem('selectedProgramId', programId);
        });
    });

    function getProgramsByGroup(groupId, selectedProgramId) {
        $.ajax({
            url: '/get-programs-group/' + groupId,
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