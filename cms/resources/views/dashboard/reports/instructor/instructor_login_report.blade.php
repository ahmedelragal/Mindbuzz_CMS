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
                                    <h5 class="title">Teacher Login Report</h5>
                                </div>
                                <div class="card-body">

                                    <form method="GET" action="{{ route('reports.teacherLoginReport') }}">
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
                                                <label for="teacher_id">Select Teacher</label>
                                                <select class="form-select js-select2" name="teacher_id" id="teacher_id">
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Teacher</option>
                                                    @endrole
                                                    @role('school')
                                                    @php
                                                    $schTeachers = App\Models\User::where('school_id', auth()->user()->school_id)
                                                    ->where('role', 1)
                                                    ->get();
                                                    @endphp
                                                    @foreach ($schTeachers as $teacher)
                                                    <option value="{{ $teacher->id }}">
                                                        {{ $teacher->name }}
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
                                    @if(isset($teacherName) && isset($numLogin))
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

@if(isset($teacherName) && isset($numLogin))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from your controller
        var teacherNames = @json($teacherName);
        var numLogins = @json($numLogin);
        var maxLogins = Math.max(...numLogins);

        // Create the bar chart
        var ctx = document.getElementById('loginChart').getContext('2d');
        var loginChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: teacherNames,
                datasets: [{
                    label: 'Number of Logins',
                    data: numLogins,
                    backgroundColor: '#f4bd0e',
                    borderColor: '#f4bd0e',
                    borderWidth: 1,
                    barThickness: 100
                }]
            },
            options: {
                scales: {
                    x: {
                        min: 0,
                        max: teacherNames.length > 1 ? teacherNames.length - 1 : 1,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        min: 0,
                        max: maxLogins + 10,
                        ticks: {
                            stepSize: 1
                        }
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        var selectedTeacherId = "{{ $request['teacher_id'] ?? '' }}";
        $('#sch_id').change(function() {
            var schoolId = $('#sch_id option:selected').data('school');
            getSchoolTeachers(schoolId, selectedTeacherId);
        });

        $('#sch_id').trigger('change');
    });

    function getSchoolTeachers(schoolId, selectedTeacherId) {
        $.ajax({
            url: '/get-teachers-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="teacher_id"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="teacher_id"]').append(
                        '<option value="" selected disabled>No Available Teacher</option>'
                    );
                } else {
                    $('select[name="teacher_id"]').append(
                        '<option value="" selected disabled>Choose a Teacher</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="teacher_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedTeacherId) {
                        $('select[name="teacher_id"]').val(selectedTeacherId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error fetching teachers:', error);
            }
        });
    }
</script>

@endsection