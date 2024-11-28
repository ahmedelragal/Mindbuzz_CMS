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
                                    <h5 class="title">Class Number of Trials Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classNumOfTrialsReportWeb') }}">
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
                                                    <option value="" selected disabled>All Programs</option>
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
                                        </div>
                                        <div class="row mt-4">
                                            <!-- From Date Filter -->
                                            <div class="col-md-4">
                                                <label for="from_date">From Date</label>
                                                <input type="date" class="form-control" name="from_date" id="from_date" value="{{ old('from_date', $request['from_date'] ?? '') }}">
                                            </div>

                                            <!-- To Date Filter -->
                                            <div class="col-md-4">
                                                <label for="to_date">To Date</label>
                                                <input type="date" class="form-control" name="to_date" id="to_date" value="{{ old('to_date', $request['to_date'] ?? '') }}">
                                            </div>
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
                        @if (isset($progress))
                        @if ($progress->first() != null)
                        <section id="reports-section">
                            <div class="card mt-4">
                                <div class="card-body">
                                    <div class="containerchart" style="display: flex;align-items: center;justify-content: center;">
                                        <div>
                                            <canvas id="trialspieChart" width="600" height="600"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card mt-4">
                                <div class="card-body">
                                    <!-- <h5>Monthly Scores</h5>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Month</th>
                                                    <th>Total Score</th>
                                                    <th>Tests</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tprogress as $month => $monthlyScore)
                                                <tr>
                                                    <td>{{ $monthlyScore['month'] }}</td>
                                                    <td>{{ $monthlyScore['total_score'] }}</td>
                                                    <td>
                                                        @foreach ($monthlyScore['tests'] as $test)
                                                        <div>{{ $test['name'] }}: {{ $test['score'] }}</div>
                                                        @endforeach
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table> -->

                                    <h5 class="mb-3">Details</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                @if ($ProgramFlag == 0)
                                                <th>Program</th>
                                                @endif
                                                <th>Unit</th>
                                                <th>Lesson</th>
                                                <th>Game</th>
                                                <th>Test Name</th>
                                                <th>Number of Trials</th>
                                                <th>Score</th>
                                                <th>Started At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($progress as $prog)
                                            <tr>
                                                @php
                                                $program = App\Models\Program::find($prog->program_id);
                                                $gameName = App\Models\Game::find(App\Models\Test::find($prog->test_id)->game_id)->name
                                                @endphp
                                                <td>{{ App\Models\User::find($prog->student_id)->name }}</td>
                                                @if ($ProgramFlag == 0)
                                                <td>{{ $program->course->name . ' - ' . $program->stage->name }}</td>
                                                @endif
                                                <td>{{ App\Models\Unit::find($prog->unit_id)->name }}</td>
                                                <td>{{App\Models\Lesson::find($prog->lesson_id)->name }}</td>
                                                <td>{{ $gameName }}</td>
                                                <td>{{ App\Models\Test::find($prog->test_id)->name }}</td>
                                                <td>{{ $prog->mistake_count + 1 }}</td>
                                                <td>{{ $prog->score }}</td>
                                                <td>{{ $prog->created_at->format('Y-m-d') }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection
@php
if(isset($progress)){
$data = [$oneStarDisplayedPercentage, $twoStarDisplayedPercentage, $threeStarDisplayedPercentage];
}
@endphp

@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($data))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var values = @json($data);
        const labels = ['First Trial', 'Second Trial', 'Third Trial or More'];
        var ctx = document.getElementById("trialspieChart").getContext("2d");

        // Destroy previous chart instance if it exists
        if (window.trialsChart) {
            window.trialsChart.destroy();
        }
        window.trialsChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Percentage',
                    data: values,
                    backgroundColor: ['#1cd0a0', '#f4bd0eb3', '#d84d42'],
                    borderColor: ['#1cd0a0', '#f4bd0eb3', '#d84d42'],
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

</script>
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
</script>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>