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
                                        @csrf
                                        <div class="row">
                                            <!-- Group Filter -->
                                            <div class="col-md-4">
                                                <label for="group_id">Select school/class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled selected>Choose a school/class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}" {{ old('group_id', $request['group_id'] ?? '') == $group->id ? 'selected' : '' }}>
                                                        {{ $sch->name }} / {{ $group->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Program Filter -->
                                            <div class="col-md-4">
                                                <label for="program_id">Select Program</label>
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" disabled selected>Choose a Program</option>
                                                    @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}">
                                                        {{ $program->course ? $program->course->name : 'No Course' }} /
                                                        {{ $program->stage ? $program->stage->name : 'No Stage' }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                            </div>

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

                                            <!-- Submit Button -->
                                            <div class="col-md-4 mt-4">
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>

                            <!-- Report Section -->
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
                                                    <th>Test Name</th>
                                                    <th>Score</th>
                                                    <th>Mistake Count</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($progress as $prog)
                                                <tr>
                                                    <td>{{ App\Models\User::find($prog->student_id)->name }}</td>
                                                    <td>{{ App\Models\Test::find($prog->test_id)->name }}</td>
                                                    <td>{{ $prog->score }}</td>
                                                    <td>{{ $prog->mistake_count }}</td>
                                                    <td>{{ $prog->created_at }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
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
@php
$data = [$oneStarDisplayedPercentage, $twoStarDisplayedPercentage, $threeStarDisplayedPercentage];
@endphp

@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($data))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var values = @json($data);
        const labels = ['First Trial', 'Second Trial', 'Third Trial'];
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

@if(session('error'))
<script>
    Swal.fire({
        title: @json(session('error')),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
    var element = document.getElementById("reports-section");
    element.style.display = 'none'
</script>
@endif
</script>
<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        $('#group_id').change(function() {
            var schoolId = $('#group_id option:selected').data('school');
            var groupId = $('#group_id').val();
            // console.log(schoolId, groupId);
            getProgramsByGroup(schoolId, groupId);
        });
    });

    function getProgramsByGroup(schoolId, groupId) {
        $.ajax({
            url: '/get-programs-group/' + schoolId + '/' + groupId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="program_id"]').empty();
                $('select[name="program_id"]').append(
                    '<option value="">Choose a Program</option>');
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>