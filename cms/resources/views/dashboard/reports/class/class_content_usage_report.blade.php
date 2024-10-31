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
                                    <h5 class="title">Class Content Usage Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classContentUsageReport') }}">
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
                                                    <option value="" selected>No Available Programs</option>

                                                </select>
                                            </div>

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
                            @if (isset($programsUsage) || isset($unitsUsage) || isset($lessonsUsage) || isset($gamesUsage) || isset($skillsUsage))
                            <div id="report_container">
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <!-- Display Chart if Data is Available -->
                                        <div class="container mt-5">
                                            <div class="chart-buttons" id="chart-buttons" style="display: none; justify-content: flex-end; gap: 10px; padding-top:20px">
                                                <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous Unit</button>
                                                <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next Unit</button>
                                            </div>
                                            <canvas id="usageChart" width="400" height="200"></canvas>
                                        </div>

                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        @if (isset($programsUsage) || isset($unitsUsage) || isset($lessonsUsage) || isset($gamesUsage) || isset($skillsUsage))
                                        @if (isset($programsUsage))
                                        <h5>Programs Usage</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Program</th>
                                                    <th>Usage Percentage(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($programsUsage as $program)
                                                <tr>
                                                    <td>{{ $program['name'] }}</td>
                                                    <td>{{ $program['usage_percentage'] }}%</td>
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
                                                    <th>Usage Percentage(%)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($unitsUsage as $unit)
                                                <tr>
                                                    <td>{{ $unit['name'] }}</td>
                                                    <td>{{ $unit['usage_percentage'] }}%</td>
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
                                                    <th>Usage Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($lessonsUsage as $unit)
                                                <?php $inc = 0; ?>
                                                @foreach ($unit['lessons'] as $lesson)
                                                @if ($inc == 0)
                                                <tr>
                                                    <td>{{$unit['name']}}</td>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td> {{$lesson['usage_percentage']}}%</td>
                                                </tr>
                                                <?php $inc = 1; ?>
                                                @else
                                                <tr>
                                                    <td></td>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td> {{$lesson['usage_percentage']}}%</td>
                                                </tr>
                                                @endif

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
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($gamesUsage as $unit)
                                                @foreach ($unit['lessons'] as $lesson)

                                                <tr>
                                                    <td>{{$lesson['name']}}</td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                @foreach ($lesson['games'] as $game)
                                                <tr>
                                                    <td></td>
                                                    <td>{{$game['name']}}</td>
                                                    <td> <?php echo $game['assigned'] == 1 ? 'Assigned' : 'Unassigned'; ?></td>
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

                                        @else
                                        <p>No data available for the selected filters.</p>
                                        @endif
                                    </div>
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
@if (isset($gamesLabels) || isset($gamesValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller
        const gamesLabels = @json($gamesLabels);
        const gamesValues = @json($gamesValues);

        // Group lessons by unit using the "-" separator
        const units = [];
        let currentUnit = [];
        gamesLabels.forEach((label, index) => {
            if (label !== "-") {
                currentUnit.push({
                    label: label,
                    value: gamesValues[index]
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

        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';
        toggleButtons();
        // Initialize the chart with the first unit's data
        let usageChart = initializeChart(ctx, units[currentPage].map(item => item.label), units[currentPage].map(item => item.value));

        // Function to initialize chart
        function initializeChart(ctx, labels, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Assigned Games',
                        data: data, // Expecting values between 0 and 100
                        backgroundColor: '#E9C874',
                        borderColor: '#E9C874',
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
                            max: 1, // Set the max value to 100 for percentage
                        }
                    },
                    responsive: true,
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
        }



        // Function to update the chart with the current page data (current unit)
        function updateChart() {
            const currentUnit = units[currentPage];
            if (usageChart) {
                usageChart.data.labels = currentUnit.map(item => item.label);
                usageChart.data.datasets[0].data = currentUnit.map(item => item.value);
                usageChart.update();
            }
            toggleButtons();
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

@if (isset($chartLabels) || isset($chartValues))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from controller
        const names = @json($chartLabels);
        const usageCounts = @json($chartValues);

        // Group lessons by unit using the "-" separator
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
        // Push the last unit if it's not empty
        if (currentUnit.length > 0) {
            units.push(currentUnit);
        }

        // Initialize dynamic pagination variables
        let currentPage = 0;

        const ctx = document.getElementById('usageChart').getContext('2d');
        const btnContainer = document.getElementById('chart-buttons').style.display = 'flex';
        toggleButtons();
        // Initialize the chart with the first unit's data
        let usageChart = initializeChart(ctx, units[currentPage].map(item => item.label), units[currentPage].map(item => item.value));

        // Function to initialize chart
        function initializeChart(ctx, labels, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Usage',
                        data: data, // Expecting values between 0 and 100
                        backgroundColor: '#E9C874',
                        borderColor: '#E9C874',
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
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    let value = tooltipItem.raw; // Get the value from the tooltip item
                                    return `${value}%`; // Append '%' to the tooltip value
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
                usageChart.data.labels = currentUnit.map(item => item.label);
                usageChart.data.datasets[0].data = currentUnit.map(item => item.value);
                usageChart.update();
            }
            toggleButtons();
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

                // Append the "All Programs" option
                $('select[name="program_id"]').append(
                    '<option value="" selected>All Programs</option>');

                // Append the fetched program options
                $.each(data, function(key, value) {
                    $('select[name="program_id"]').append('<option value="' +
                        value.id + '">' + value.program_details + '</option>');
                });

                // Re-select the program_id if it exists in localStorage
                if (selectedProgramId) {
                    $('select[name="program_id"]').val(selectedProgramId).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>

@endsection