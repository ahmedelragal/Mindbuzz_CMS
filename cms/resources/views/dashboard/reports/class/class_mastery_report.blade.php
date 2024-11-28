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
                                    <h5 class="title">Class Mastery Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classMasteryReportWeb') }}">
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
                                                    <!-- <option value="" selected>No Filter</option> -->
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
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
                                        </div>
                                        <!-- Submit Button -->
                                        <div class="col-md-4 mt-4">
                                            <button type="submit" class="btn btn-primary">Filter</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <!-- Report Section -->
                            <section id="reports-section">
                                @if(isset($chartLabels) || isset($chartValues) || isset($gamesLabels) || isset($gamesValues))
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <!-- Display Chart if Data is Available -->
                                        <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                            <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                            <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</span></button>
                                        </div>
                                        <div class="container mt-5">
                                            <canvas id="masteryChart" width="400" height="200"></canvas>
                                        </div>

                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        @if (isset($unitsMastery) || isset($lessonsMastery) || isset($gamesMastery) || isset($skillsMastery))

                                        @if (isset($unitsMastery))
                                        <h5>Units Mastery</h5>
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Introduced</th>
                                                    <th>Practiced</th>
                                                    <th>Mastered</th>
                                                    <th>Mastery Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unitsMastery as $unit)
                                                <tr>
                                                    <td>{{ $unit['name'] }}</td>
                                                    <td>{{ $unit['introduced'] }}</td>
                                                    <td>{{ $unit['practiced'] }}</td>
                                                    <td>{{ $unit['mastered'] }}</td>
                                                    <td>{{ $unit['mastery_percentage'] }}%</td>
                                                </tr>

                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($lessonsMastery))
                                        <h5>Lessons Mastery</h5>
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th class="col-1">Unit</th>
                                                    <th class="col-1">Lesson</th>
                                                    <th class="col-1">Introduced</th>
                                                    <th class="col-1">Practiced</th>
                                                    <th class="col-1">Mastered</th>
                                                    <th class="col-1">Mastery Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lessonsMastery as $unit)
                                                <?php $inc = 0; ?>
                                                @foreach ($unit['lessons'] as $lesson)


                                                @if ($inc==0)
                                                <tr>
                                                    <td>{{$unit['name']}}</td>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td>{{ $lesson['introduced'] }}</td>
                                                    <td>{{ $lesson['practiced'] }}</td>
                                                    <td>{{ $lesson['mastered'] }}</td>
                                                    <td>{{ $lesson['mastery_percentage'] }}%</td>
                                                </tr>
                                                <?php $inc = 1; ?>
                                                @else
                                                <tr>
                                                    <td></td>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td>{{ $lesson['introduced'] }}</td>
                                                    <td>{{ $lesson['practiced'] }}</td>
                                                    <td>{{ $lesson['mastered'] }}</td>
                                                    <td>{{ $lesson['mastery_percentage'] }}%</td>
                                                </tr>
                                                @endif


                                                @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($gamesMastery))

                                        <h5>Games Mastery</h5>
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Lesson</th>
                                                    <th>Game</th>
                                                    <th>Introduced</th>
                                                    <th>Practiced</th>
                                                    <th>Mastered</th>
                                                    <th>Mastery Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @foreach ($gamesMastery as $unit)
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
                                                    <td>{{ $game['introduced'] }}</td>
                                                    <td>{{ $game['practiced'] }}</td>
                                                    <td>{{ $game['mastered'] }}</td>
                                                    <td>{{ $game['mastery_percentage'] }}%</td>
                                                </tr>
                                                @endforeach
                                                @endforeach
                                                @endforeach


                                            </tbody>
                                        </table>
                                        @endif

                                        @if (isset($skillsMastery))
                                        <h5>Skills Mastery</h5>
                                        <table class="table mt-2">
                                            <thead>
                                                <tr>
                                                    <th>Unit</th>
                                                    <th>Lesson</th>
                                                    <th>Game</th>
                                                    <th>Skill</th>
                                                    <th>Introduced</th>
                                                    <th>Practiced</th>
                                                    <th>Mastered</th>
                                                    <th>Mastery Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>



                                                @foreach ($skillsMastery as $unit)
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
                                                    <td>{{ $skill['introduced'] }}</td>
                                                    <td>{{ $skill['practiced'] }}</td>
                                                    <td>{{ $skill['mastered'] }}</td>
                                                    <td>{{ $skill['mastery_percentage'] }}%</td>
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
                            </section>
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

        const ctx = document.getElementById('masteryChart').getContext('2d');
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
                if (value <= 30) return '#ff3030'; // Red for ≤30%
                if (value <= 60) return '#f7d156'; // Yellow for 31%-60%
                return '#1cd0a0'; // Green for 61%-100%
            }

            // Generate the backgroundColor array dynamically
            const backgroundColors = data.map(value => getBarColor(value));

            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels, // x-axis labels
                    datasets: [{
                        label: 'Mastery Percentage',
                        data: data, // bar values
                        chartNames: chartNames, // store the game names
                        backgroundColor: backgroundColors, // Dynamically set colors
                        borderColor: backgroundColors, // Match border color with bar color
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
                                            text: 'Introduced (≤ 30%)',
                                            fillStyle: '#ff3030', // Red
                                            strokeStyle: '#ff3030',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Practiced (31% - 60%)',
                                            fillStyle: '#f7d156', // Yellow
                                            strokeStyle: '#f7d156',
                                            lineWidth: 0
                                        },
                                        {
                                            text: 'Mastered (> 60%)',
                                            fillStyle: '#1cd0a0', // Green
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
                    if (item.value <= 30) return '#ff3030'; // Red for ≤30%
                    if (item.value <= 60) return '#f7d156'; // Yellow for 31%-60%
                    return '#1cd0a0'; // Green for 61%-100%
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!-- SweetAlert validation messages -->
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