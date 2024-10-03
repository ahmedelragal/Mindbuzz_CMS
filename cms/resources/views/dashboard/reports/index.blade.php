@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap">
            @include('dashboard.layouts.navbar')
            <div class="nk-content">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="title">Student Progress Reports</h5>
                                </div>
                                <div class="card-body">
                                    <form id="student-selection-form">
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                @role('Admin')
                                                <label for="sch_id">Select School</label>
                                                <select class="form-select js-select2" name="sch_id"
                                                    id="sch_id" required>
                                                    <option value="" selected disabled>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{$school->id}}">{{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole

                                                @role('school')
                                                <input type="hidden" value="{{auth()->user()->school_id}}">
                                                @endrole
                                            </div>
                                        </div>
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id"
                                                    id="student_id" required>
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Student</option>
                                                    <!-- @foreach ($students as $student)
                                                    <option value="{{ $student->id }}">{{ $student->name }}
                                                    </option>
                                                    @endforeach -->
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schStudents = App\Models\User::where('school_id', auth()->user()->school_id)->where('role', 2)->where('is_student', 1)->get();
                                                    @endphp
                                                    @foreach ($schStudents as $student)
                                                    <option value="{{ $student->id }}">{{ $student->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>

                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" class="btn btn-primary mt-4"
                                                    id="fetch-reports">View Reports</button>
                                            </div>
                                        </div>
                                    </form>
                                    <ul class="nav nav-tabs mt-4" id="reportTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="completion-report-tab" data-toggle="tab"
                                                href="#completion-report" role="tab"
                                                aria-controls="completion-report" aria-selected="true">Completion
                                                Report</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="mastery-report-tab" data-toggle="tab"
                                                href="#mastery-report" role="tab" aria-controls="mastery-report"
                                                aria-selected="false">Mastery Report</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="num-of-trials-report-tab" data-toggle="tab"
                                                href="#num-of-trials-report" role="tab"
                                                aria-controls="num-of-trials-report" aria-selected="false">Number of
                                                Trials Report</a>
                                        </li>
                                        <!-- <li class="nav-item">
                                            <a class="nav-link" id="num-of-login-report-tab" data-toggle="tab"
                                                href="#num-login-report" role="tab"
                                                aria-controls="num-of-login-report" aria-selected="false">Number of
                                                Logins Report</a>
                                        </li> -->
                                        {{-- <li class="nav-item">
                                                <a class="nav-link" id="skill-report-tab" data-toggle="tab"
                                                    href="#skill-report" role="tab" aria-controls="skill-report"
                                                    aria-selected="false">Skill Report</a>
                                            </li> --}}
                                    </ul>
                                    <div class="tab-content mt-4">
                                        <!-- Completion Report Tab -->
                                        <div class="tab-pane fade show active" id="completion-report" role="tabpanel"
                                            aria-labelledby="completion-report-tab">
                                            <div class="filter-form-container">
                                                <form class="filter-form" method="GET"
                                                    action="{{ route('reports.completionReport') }}">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label for="program_id"><b>Program</b></label>
                                                            @role('Admin')
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id">
                                                                @foreach ($programs as $program)
                                                                <option value="{{ $program->id }}">
                                                                    @if ($program->course)
                                                                    {{ $program->name . '/' . $program->course->name }}
                                                                    @if ($program->stage)
                                                                    {{ ' / ' . $program->stage->name }}
                                                                    @endif
                                                                    @else
                                                                    {{ $program->name }}
                                                                    @endif
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                            @role('school')
                                                            @php
                                                            $schPrograms = App\Models\Program::with('course')
                                                            ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                                                            ->join('courses', 'programs.course_id', '=', 'courses.id')
                                                            ->join('stages', 'programs.stage_id', '=', 'stages.id')
                                                            ->where('school_programs.school_id', auth()->user()->school_id)
                                                            ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                                                            ->get();
                                                            @endphp
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id" required>
                                                                <option value="" selected disabled>Choose a
                                                                    program</option>
                                                                @foreach ($schPrograms as $program)
                                                                <option value="{{ $program->id }}">
                                                                    {{$program->program_details}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="from_date"><b>From Date</b></label>
                                                            <input type="date" class="form-control" name="from_date"
                                                                id="from_date">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="to_date"><b>To Date</b></label>
                                                            <input type="date" class="form-control" name="to_date"
                                                                id="to_date">
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label for="status"><b>Status</b></label>
                                                            <select class="form-select js-select2" name="status"
                                                                id="status">
                                                                <option value="" selected>All Status</option>
                                                                <option value="Completed">Completed</option>
                                                                <option value="Overdue">Overdue</option>
                                                                <option value="Pending">Pending</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    {{-- <div class="form-row mt-3">
                                                            <div class="col-md-12 text-right">
                                                                <button type="submit"
                                                                    class="btn btn-primary">Filter</button>
                                                            </div>
                                                        </div> --}}
                                                </form>
                                            </div>
                                            <!-- Chart Placeholder -->
                                            {{-- <div class="chart-container mt-4">
                                                    <canvas id="progressChart"
                                                        style="max-width: 100%; height: 400px;"></canvas>
                                                </div> --}}
                                            <section class="mt-4">
                                                <div class="containerchart" style="display: flex;align-items: center;justify-content: center;">
                                                    {{-- <h2>Chart.js Responsive Bar Chart Demo</h2> --}}
                                                    <div>
                                                        <canvas id="completionpieChart" width="600" height="600" style="display:none;"></canvas>
                                                    </div>

                                                </div>
                                            </section>

                                            <div class="report-container mt-4"></div>
                                        </div>
                                        <!-- Mastery Report Tab -->
                                        <div class="tab-pane fade" id="mastery-report" role="tabpanel"
                                            aria-labelledby="mastery-report-tab">
                                            <div class="filter-form-container">
                                                <form class="filter-form" method="GET"
                                                    action="{{ route('reports.masteryReport') }}">
                                                    <div class="form-row">
                                                        <div class="col-md-3">
                                                            <label for="program_id"><b>Program</b></label>
                                                            @role('Admin')
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id" required>
                                                                <option value="" selected disabled>Choose a
                                                                    program</option>
                                                                @foreach ($programs as $program)
                                                                <option value="{{ $program->id }}">
                                                                    @if ($program->course)
                                                                    {{ $program->name . '/' . $program->course->name }}
                                                                    @if ($program->stage)
                                                                    {{ ' / ' . $program->stage->name }}
                                                                    @endif
                                                                    @else
                                                                    {{ $program->name }}
                                                                    @endif

                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                            @role('school')
                                                            @php
                                                            $schPrograms = App\Models\Program::with('course')
                                                            ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                                                            ->join('courses', 'programs.course_id', '=', 'courses.id')
                                                            ->join('stages', 'programs.stage_id', '=', 'stages.id')
                                                            ->where('school_programs.school_id', auth()->user()->school_id)
                                                            ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                                                            ->get();

                                                            @endphp
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id" required>
                                                                <option value="" selected disabled>Choose a
                                                                    program</option>
                                                                @foreach ($schPrograms as $program)
                                                                <option value="{{ $program->id }}">
                                                                    {{$program->program_details}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label><b>Filter By</b></label>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="filter_type" id="filter_unit"
                                                                    value="unit" checked>
                                                                <label class="form-check-label"
                                                                    for="filter_unit">Unit</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="filter_type" id="filter_lesson"
                                                                    value="lesson">
                                                                <label class="form-check-label"
                                                                    for="filter_lesson">Lesson</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="filter_type" id="filter_game"
                                                                    value="game">
                                                                <label class="form-check-label"
                                                                    for="filter_game">Game</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio"
                                                                    name="filter_type" id="filter_skill"
                                                                    value="skill">
                                                                <label class="form-check-label"
                                                                    for="filter_skill">Skill</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-3">
                                                            <label for="from_date"><b>From Date</b></label>
                                                            <input type="date" class="form-control"
                                                                name="from_date" id="from_date">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="to_date"><b>To Date</b></label>
                                                            <input type="date" class="form-control" name="to_date"
                                                                id="to_date">
                                                        </div>
                                                        {{-- <div class="col-md-12 text-right mt-3">
                                                                <button type="submit"
                                                                    class="btn btn-primary">Filter</button>
                                                            </div> --}}
                                                    </div>
                                                </form>
                                            </div>
                                            <section class="mt-4">
                                                <div class="containerchart" style="max-width: 600px; max-height: 600px;">
                                                    {{-- <h2>Chart.js Responsive Bar Chart Demo</h2> --}}
                                                    <div>
                                                        <canvas id="masterybarChart" width="200" height="200" style="display:none;"></canvas>
                                                    </div>

                                                </div>
                                            </section>
                                            <div class="report-container mt-4"></div>
                                        </div>

                                        <!-- Number of Trials Report Tab -->
                                        <div class="tab-pane fade" id="num-of-trials-report" role="tabpanel"
                                            aria-labelledby="num-of-trials-report-tab">
                                            <div class="filter-form-container">
                                                <form class="filter-form" method="GET"
                                                    action="{{ route('reports.numOfTrialsReport') }}">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label for="program_id"><b>Program</b></label>
                                                            @role('Admin')
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id" required>
                                                                <option value="" selected disabled>Choose a
                                                                    program</option>
                                                                @foreach ($programs as $program)
                                                                <option value="{{ $program->id }}">
                                                                    @if ($program->course)
                                                                    {{ $program->name . '/' . $program->course->name }}
                                                                    @if ($program->stage)
                                                                    {{ ' / ' . $program->stage->name }}
                                                                    @endif
                                                                    @else
                                                                    {{ $program->name }}
                                                                    @endif

                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                            @role('school')
                                                            @php
                                                            $schPrograms = App\Models\Program::with('course')
                                                            ->join('school_programs', 'programs.id', '=', 'school_programs.program_id')
                                                            ->join('courses', 'programs.course_id', '=', 'courses.id')
                                                            ->join('stages', 'programs.stage_id', '=', 'stages.id')
                                                            ->where('school_programs.school_id', auth()->user()->school_id)
                                                            ->select('programs.*', DB::raw("CONCAT(courses.name, ' - ', stages.name) as program_details"))
                                                            ->get();

                                                            @endphp
                                                            <select class="form-select js-select2" name="program_id"
                                                                id="program_id" required>
                                                                <option value="" selected disabled>Choose a
                                                                    program</option>
                                                                @foreach ($schPrograms as $program)
                                                                <option value="{{ $program->id }}">
                                                                    {{$program->program_details}}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @endrole
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="from_date"><b>From Date</b></label>
                                                            <input type="date" class="form-control"
                                                                name="from_date" id="from_date">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="to_date"><b>To Date</b></label>
                                                            <input type="date" class="form-control" name="to_date"
                                                                id="to_date">
                                                        </div>
                                                    </div>
                                                    {{-- <div class="form-row mt-3">
                                                            <div class="col-md-12 text-right">
                                                                <button type="submit"
                                                                    class="btn btn-primary">Filter</button>
                                                            </div>
                                                        </div> --}}
                                                </form>
                                            </div>
                                            <section class="mt-4">
                                                <div class="containerchart" style="max-width: 800px; max-height: 700px;">
                                                    {{-- <h2>Chart.js Responsive Bar Chart Demo</h2> --}}
                                                    <div>
                                                        <canvas id="trialsbarChart" style="display:none;"></canvas>
                                                    </div>

                                                </div>
                                            </section>
                                            <div class="report-container mt-4"></div>
                                        </div>
                                        <!-- Number of logins report tab
                                        <div class="tab-pane fade" id="num-login-report" role="tabpanel"
                                            aria-labelledby="login-report-tab">
                                            <div class="filter-form-container">
                                                <form class="filter-form" method="GET"
                                                    action="{{ route('reports.studentLoginReport') }}">
                                                    <div class="form-row">
                                                        <div class="col-md-3">



                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                            <section class="mt-4">
                                                <div class="containerchart">
                                                    {{-- <h2>Chart.js Responsive Bar Chart Demo</h2> --}}
                                                    <div>
                                                        <canvas id="barChart"></canvas>
                                                    </div>

                                                </div>
                                            </section>
                                            <div class="report-container mt-4"></div>
                                        </div> -->

                                        <!-- Skill Report Tab -->
                                        <div class="tab-pane fade" id="skill-report" role="tabpanel"
                                            aria-labelledby="skill-report-tab">
                                            <div class="filter-form-container">
                                                <form class="filter-form" method="GET"
                                                    action="{{ route('reports.skillReport') }}">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <label for="program_id"><b>Program</b></label>
                                                            <select class="form-select js-select2" name="program_id " required
                                                                id="program_id" required>
                                                                @foreach ($programs as $program)
                                                                <option value="{{ $program->id }}">
                                                                    @if ($program->course)
                                                                    {{ $program->name . '/' . $program->course->name }}
                                                                    @if ($program->stage)
                                                                    {{ ' / ' . $program->stage->name }}
                                                                    @endif
                                                                    @else
                                                                    {{ $program->name }}
                                                                    @endif

                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="skill_id"><b>Skill</b></label>
                                                            <select class="form-select js-select2" name="skill_id"
                                                                id="skill_id">
                                                                @foreach ($skills as $skill)
                                                                <option value="{{ $skill->id }}">
                                                                    {{ $skill->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="from_date"><b>From Date</b></label>
                                                            <input type="date" class="form-control"
                                                                name="from_date" id="from_date">
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label for="to_date"><b>To Date</b></label>
                                                            <input type="date" class="form-control" name="to_date"
                                                                id="to_date">
                                                        </div>
                                                    </div>
                                                    {{-- <div class="form-row mt-3">
                                                            <div class="col-md-12 text-right">
                                                                <button type="submit"
                                                                    class="btn btn-primary">Filter</button>
                                                            </div>
                                                        </div> --}}
                                                </form>
                                            </div>
                                            <div class="report-container mt-4"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        // Initialize select2 for the filters
        $('.js-select2').select2();

        // Function to fetch and display reports
        function fetchReport(url, form, container) {
            $.ajax({
                url: url,
                method: 'GET',
                data: form.serialize(),
                beforeSend: function() {
                    container.html(
                        '<div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div>'
                    );
                },
                success: function(response) {
                    container.html(renderReport(response));

                    // Render the chart if data is available and it's the Mastery Report tab
                    if (response.length > 0 && response[0].mastery_percentage !== undefined) {
                        document.getElementById('masterybarChart').style.display = 'block';
                        renderMasteryChart(response);
                    }

                    if (response.counts != undefined && !(response.counts.completed == 0 && response.counts.overdue == 0 && response.counts.pending == 0)) {
                        document.getElementById('completionpieChart').style.display = 'block';
                        renderCompletionChart(response.counts);
                    }
                    if (response[0] && response[0].num_trials !== undefined) {
                        document.getElementById('trialsbarChart').style.display = 'block';
                        renderTrialsChart(response);
                    }

                },
                error: function(xhr, status, error) {
                    container.html(
                        '<div class="alert alert-danger">Error fetching data. Please try again.</div>'
                    );
                }
            });
        }

        function renderCompletionChart(data) {
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
        }
        // Function to render the chart
        function renderMasteryChart(data) {
            // const ctx = document.getElementById('barChart').getContext('2d');
            // var index = 11;
            var ctx = document.getElementById("masterybarChart").getContext("2d");
            // Destroy previous chart instance if it exists
            if (window.masteryChart) {
                window.masteryChart.destroy();
            }

            // Extract labels and data points from the response
            const labels = data.map(item => item.name);
            const failedData = data.map(item => item.failed);
            const introducedData = data.map(item => item.introduced);
            const practicedData = data.map(item => item.practiced);
            const masteredData = data.map(item => item.mastered);
            const masteredPercentage = data.map(item => item.mastery_percentage);

            // Create the new chart with the actual data
            window.masteryChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Mastery Percentage',
                        data: masteredPercentage,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        barThickness: 100
                    }]
                },
                options: {
                    scales: {
                        x: {
                            min: 0,
                            max: labels.length > 1 ? labels.length - 1 : 1,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
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
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + '%';
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

        function renderTrialsChart(data) {
            const ctx = document.getElementById('trialsbarChart').getContext('2d');
            console.log(data);
            // Destroy previous chart instance if it exists
            if (window.trialsChart) {
                window.trialsChart.destroy();
            }

            // Extract labels and data points from the response
            const labels = data.map(item => item.test_name);
            const trialsData = data.map(item => item.num_trials);
            const scoreData = data.map(item => item.score);

            // Create the new chart with the actual data
            window.trialsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Trial Count',
                        data: trialsData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        maxBarThickness: 100
                    }]
                },
                options: {
                    scales: {
                        x: {
                            min: 0,
                            max: labels.length > 1 ? labels.length - 1 : 1,
                            grid: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                // callback: function(value) {
                                //     if (Number.isInteger(value)) {
                                //         return value;
                                //     }
                                // }
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
        // Function to render report HTML
        function renderReport(data) {
            let html = '';

            if (data.counts != undefined && !(data.counts.completed == 0 && data.counts.overdue == 0 && data.counts.pending == 0)) {
                html += `<h4>Latest Progress:</h4>`;
                html += `
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">Completed</div>
                        <div class="card-body">
                            <h5 class="card-title">${data.counts.completed}</h5>
                            <p class="card-text">${data.assignments_percentages.completed}%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-header">Overdue</div>
                        <div class="card-body">
                            <h5 class="card-title">${data.counts.overdue}</h5>
                            <p class="card-text">${data.assignments_percentages.overdue}%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Pending</div>
                        <div class="card-body">
                            <h5 class="card-title">${data.counts.pending}</h5>
                            <p class="card-text">${data.assignments_percentages.pending}%</p>
                        </div>
                    </div>
                </div>
            </div>
            <table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Start Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>`;

                data.tests.forEach(test => {
                    html += `
                <tr>
                    <td>${test.tests.name}</td>
                    <td>${test.start_date}</td>
                    <td>${test.due_date}</td>
                    <td>${test.status == 1 ? 'Completed' : (new Date(test.due_date) < new Date() ? 'Overdue' : 'Pending')}</td>
                </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else if (data[0] && data[0].unit_id !== undefined) {
                html += `<h5>Units Mastery</h5>`;
                html += `<table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Unit</th>
                        <th>Introduced</th>
                        <th>Practiced</th>
                        <th>Mastered</th>
                        <th>Mastery Percentage</th>
                    </tr>
                </thead>
                <tbody>`;

                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.introduced}</td>
                        <td>${item.practiced}</td>
                        <td>${item.mastered}</td>
                        <td>${item.mastery_percentage}%</td>
                    </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else if (data[0] && data[0].lesson_id !== undefined) {
                html += `<h5>Lessons Mastery</h5>`;
                html += `<table class="table table-striped mt-4">
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
                <tbody>`;

                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.failed}</td>
                        <td>${item.introduced}</td>
                        <td>${item.practiced}</td>
                        <td>${item.mastered}</td>
                        <td>${item.mastery_percentage}%</td>
                    </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else if (data[0] && data[0].game_id !== undefined) {
                html += `<h5>Games Mastery</h5>`;
                html += `<table class="table table-striped mt-4">
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
                <tbody>`;

                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.failed}</td>
                        <td>${item.introduced}</td>
                        <td>${item.practiced}</td>
                        <td>${item.mastered}</td>
                        <td>${item.mastery_percentage}%</td>
                    </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else if (data[0] && data[0].skill_id !== undefined) {
                html += `<h5>Skills Mastery</h5>`;
                html += `<table class="table table-striped mt-4">
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
                <tbody>`;

                data.forEach(item => {
                    html += `
                    <tr>
                        <td>${item.name}</td>
                        <td>${item.failed}</td>
                        <td>${item.introduced}</td>
                        <td>${item.practiced}</td>
                        <td>${item.mastered}</td>
                        <td>${item.mastery_percentage}%</td>
                    </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else if (data[0] && data[0].num_trials !== undefined) {
                // Handle other report types (e.g., trials report)
                html += `<h4>Number of Trials Report</h4>`;
                html += `<table class="table table-striped mt-4">
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Completion Date</th>
                        <th>Number of Trials</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>`;

                data.forEach(trial => {
                    html += `
                    <tr>
                        <td>${trial.test_name}</td>
                        <td>${trial.completion_date}</td>
                        <td>${trial.num_trials}</td>
                        <td>${trial.score}</td>
                    </tr>`;
                });

                html += `
                </tbody>
            </table>`;
            } else {
                html += `<div class="alert alert-danger">No Student Reports Found</div>`;
            }

            return html;
        }

        // Event listeners for the filter forms
        $('.filter-form').submit(function(e) {
            e.preventDefault();

            let form = $(this);
            let url = form.attr('action');
            let container = form.closest('.tab-pane').find('.report-container');
            fetchReport(url, form, container);
        });

        // Fetch reports when a student is selected
        $('#fetch-reports').click(function() {
            let studentId = $('#student_id').val();
            let forms = $('.filter-form');
            document.querySelectorAll('canvas').forEach(canvas => {
                canvas.style.display = 'none';
            });

            forms.each(function() {
                let form = $(this);
                let url = form.attr('action') + '?student_id=' + studentId;
                let container = form.closest('.tab-pane').find('.report-container');
                fetchReport(url, form, container);
            });
        });

        // Handle tab switch
        $('#reportTabs a').click(function(e) {
            e.preventDefault();
            $(this).tab('show');
        }).on('shown.bs.tab', function(e) {
            let target = $(e.target).attr("href"); // activated tab
            // let form = $(target).find('.filter-form');
            // if (form.length > 0) {
            //     form.trigger('submit'); // Submit the form to fetch data for the active tab
            // }

            // Remove 'show active' from all tab panes and add to the current one
            $('.tab-pane').removeClass('show active');
            $(target).addClass('show active');

            // Update the active tab link
            $('a[data-toggle="tab"]').removeClass('active');
            $(e.target).addClass('active');
            $('.alert.alert-danger').remove();
        });

        // Ensure the correct tab pane is shown on page load
        let activeTab = $('.nav-link.active').attr("href");
        $(activeTab).addClass('show active');

        // Handle filter type radio button change
        $('input[name="filter_type"]').change(function() {
            let selectedFilter = $(this).val();
            $('#filter_unit_container, #filter_lesson_container, #filter_game_container').addClass(
                'd-none');
            $(`#filter_${selectedFilter}_container`).removeClass('d-none');
        });

        // Trigger change event to show the correct filter on page load
        $('input[name="filter_type"]:checked').trigger('change');
    });
</script>

<script>
    $(document).ready(function() {
        $('#sch_id').change(function() {
            var schoolId = $('#sch_id option:selected').data('school');
            var groupId = $('#sch_id').val();
            // console.log(schoolId, groupId);
            getSchoolStudents(schoolId);
        });
    });

    function getSchoolStudents(schoolId) {
        $.ajax({
            url: '/get-students-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="student_id"]').empty();
                $('select[name="student_id"]').append(
                    '<option value="">Choose a Student</option>');
                $.each(data, function(key, value) {
                    $('select[name="student_id"]').append('<option value="' +
                        value.id + '">' +
                        value.name + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
<script>
    function randomScalingFactor() {
        return Math.round(Math.random() * 100);
    }
</script>
@endsection