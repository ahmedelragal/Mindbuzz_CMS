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
                                    <h5 class="title">Student Login Report</h5>
                                </div>
                                <div class="card-body">

                                    <form method="GET" action="{{ route('reports.studentLoginReport') }}">
                                        @csrf
                                        <div class="row">
                                            <!-- School Filter -->
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
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id" id="student_id">
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Student</option>
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schStudents = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 2)
                                                    ->where('is_student', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schStudents as $student)
                                                    <option value="{{ $student->id }}" {{ old('student_id', $request['student_id'] ?? '') == $student->id ? 'selected' : '' }}>
                                                        {{ $student->name }}
                                                    </option>
                                                    @endforeach
                                                    @endrole
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-row mt-3">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">View Report</button>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Display Chart if Data is Available -->
                                    @if(isset($studentName) && isset($numLogin))
                                    <div class="container mt-5">
                                        <canvas id="loginChart" width="400" height="200"></canvas>
                                    </div>
                                    @endif

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
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if(isset($studentName) && isset($numLogin))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from your controller
        var studentNames = @json($studentName);
        var numLogins = @json($numLogin);

        // Create the bar chart
        var ctx = document.getElementById('loginChart').getContext('2d');
        var loginChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: studentNames,
                datasets: [{
                    label: 'Number of Logins',
                    data: numLogins,
                    backgroundColor: '#E9C874', // Changed the bar color
                    borderColor: '#E9C874', // Optional: Change the border color to match
                    borderWidth: 1,
                    barThickness: 100
                }]
            },
            options: {
                scales: {
                    x: {
                        min: 0,
                        max: studentNames.length > 1 ? studentNames.length - 1 : 1, // Ensure enough space when there is only one student
                        grid: {
                            display: false // Optional: hide grid lines for better spacing effect
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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

<script>
    $(document).ready(function() {
        // Initialize select2 for the filters
        $('.js-select2').select2();

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

@endsection