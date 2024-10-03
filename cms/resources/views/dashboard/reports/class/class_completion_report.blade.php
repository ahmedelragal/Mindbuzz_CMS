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
                                    <h5 class="title">Class Completion Report</h5>
                                </div>
                                <!-- Form Section -->
                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classCompletionReportWeb') }}">
                                        @csrf
                                        <div class="row">
                                            <!-- Group Filter -->
                                            <div class="col-md-4">
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
                                            </div>

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

                                            <!-- Status Filter -->
                                            <div class="col-md-4">
                                                <label for="status">Select Status</label>
                                                <!-- <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" disabled selected>Choose a status</option>
                                                    <option value="Completed">Completed</option>
                                                    <option value="Overdue">Overdue</option>
                                                    <option value="Pending">Pending</option>
                                                </select> -->
                                                <select class="form-select js-select2" name="status" id="status">
                                                    <option value="" disabled {{ old('status', $request['status'] ?? '') == '' ? 'selected' : '' }}>Choose a status</option>
                                                    <option value="Completed" {{ old('status', $request['status'] ?? '') == 'Completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="Overdue" {{ old('status', $request['status'] ?? '') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                                                    <option value="Pending" {{ old('status', $request['status'] ?? '') == 'Pending' ? 'selected' : '' }}>Pending</option>
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

                                        <div class="form-row mt-3">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">Filter</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Report Section -->
                            <section id="reports-section">
                                @if ($counts['completed'] != 0 || $counts['overdue'] != 0 || $counts['pending'] != 0 || $assignments_percentages['completed'] || $assignments_percentages['overdue'] || $assignments_percentages['pending'] )
                                <div class="card mt-4">
                                    <div class="card-body">
                                        <div class="containerchart" style="display: flex;align-items: center;justify-content: center;">
                                            <div>
                                                <canvas id="completionpieChart" width="600" height="600"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card mt-4">
                                    <div class="card-body">
                                        <h5 class="mb-4">Details</h5>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="card text-white bg-success mb-3">
                                                    <div class="card-header">Completed</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['completed'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['completed'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-white bg-danger mb-3">
                                                    <div class="card-header">Overdue</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['overdue'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['overdue'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card text-white bg-warning mb-3">
                                                    <div class="card-header">Pending</div>
                                                    <div class="card-body">
                                                        <h5 class="card-title">{{ $counts['pending'] }}</h5>
                                                        <p class="card-text">{{ $assignments_percentages['pending'] }}%</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Student Name</th>
                                                    <th>Test Name</th>
                                                    <th>Start Date</th>
                                                    <th>Due Date</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($tests as $test)
                                                <tr>
                                                    <td>{{App\Models\User::find($test->student_id)->name}}</td>
                                                    <td>{{ $test->tests->name }}</td>
                                                    <td>{{ $test->start_date }}</td>
                                                    <td>{{ $test->due_date }}</td>
                                                    <td>{{ $test->status == 1 ? 'Completed' : (\Carbon\Carbon::parse($test->due_date)->isPast() ? 'Overdue' : 'Pending') }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </section>

                            <!-- <div style="margin-top: 20px; margin-left:10px;">
                                <p>No data available for the selected filters.</p>
                            </div> -->
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
        <!-- Footer -->
    </div>
</div>
@endsection


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($counts))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var data = @json($counts);
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
    var canvas = document.getElementById("reports-section");
    canvas.style.display = 'none';
</script>
@endif

<script>
    $(document).ready(function() {
        $('.js-select2').select2();

        $('#group_id').change(function() {
            var schoolId = $('#group_id option:selected').data('school');
            var groupId = $('#group_id').val();
            getProgramsByGroup(schoolId, groupId);
        });
        $('#group_id').trigger('change');
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