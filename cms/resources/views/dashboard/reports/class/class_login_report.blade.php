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
                                    <h5 class="title">Class Login Report</h5>
                                </div>
                                <div class="card-body">

                                    <form method="GET" action="{{ route('reports.classLoginReport') }}">
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
                                        </div>

                                        <div class="form-row mt-3">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">View Report</button>
                                            </div>
                                        </div>
                                    </form>
                                    @if(isset($studentName) && isset($numLogin))
                                    <ul class="nav nav-tabs mt-4" id="reportTabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" id="student-login-tab" data-toggle="tab"
                                                href="#student-login-report" role="tab"
                                                aria-controls="student-login-report" aria-selected="true">Students</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" id="teacher-login-tab" data-toggle="tab"
                                                href="#teacher-login" role="tab" aria-controls="teacher-login-report"
                                                aria-selected="false">Teachers</a>
                                        </li>
                                    </ul>

                                    <!-- Display Chart if Data is Available -->
                                    <div class="container mt-5">

                                        <div class="container mt-5">
                                            <div class="chart-buttons" id="chart-buttons" style="display: flex; justify-content: flex-end; gap: 10px;">
                                                <button class="btn btn-primary" id="prevBtn" onclick="previousPage()">Previous</button>
                                                <button class="btn btn-primary" id="nextBtn" onclick="nextPage()">Next</button>
                                            </div>
                                            <canvas id="studentloginChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>

                                    <div class="container mt-5">
                                        <canvas id="teacherloginChart" width="400" height="200"></canvas>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).ready(function() {
            console.log("aaa");
            // Initialize select2 for the filters
            $('.js-select2').select2();
        });
    });
</script>

@if(isset($studentName) && isset($numLogin) && isset($teacherName) && isset($teacherLogin))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pageSize = 6;
        let currentPage = 0;

        // Data from controller
        const studentNames = @json($studentName);
        const numLogins = @json($numLogin);
        const teacherNames = @json($teacherName);
        const teacherLogins = @json($teacherLogin);

        // Initialize the student login chart
        const ctx = document.getElementById('studentloginChart').getContext('2d');
        const ctx2 = document.getElementById('teacherloginChart').getContext('2d');

        let studentloginChart = initializeChart(ctx, studentNames.slice(0, pageSize), numLogins.slice(0, pageSize));

        // Function to initialize chart
        function initializeChart(ctx, labels, data) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Student Logins',
                        data: data,
                        backgroundColor: '#E9C874',
                        borderColor: '#E9C874',
                        borderWidth: 1,
                        barThickness: 120
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
                            ticks: {
                                stepSize: 1,
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: true,
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
        }

        // Initialize teacher login chart
        let teacherloginChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: teacherNames,
                datasets: [{
                    label: 'Teacher Logins',
                    data: teacherLogins,
                    backgroundColor: '#E9C874',
                    borderColor: '#E9C874',
                    borderWidth: 1,
                    barThickness: 120
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
                        ticks: {
                            stepSize: 1,
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

        // Function to get data for the current page
        function getCurrentPageData() {
            const start = currentPage * pageSize;
            const end = start + pageSize;
            return {
                labels: studentNames.slice(start, end),
                data: numLogins.slice(start, end)
            };
        }

        // Function to update the chart with the current page data
        function updateChart() {
            const pageData = getCurrentPageData();
            if (studentloginChart) {
                studentloginChart.data.labels = pageData.labels;
                studentloginChart.data.datasets[0].data = pageData.data;
                studentloginChart.update();
            }
        }

        // Function to go to the previous page
        window.previousPage = function() {
            if (currentPage > 0) {
                currentPage--;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Function to go to the next page
        window.nextPage = function() {
            if ((currentPage + 1) * pageSize < studentNames.length) {
                currentPage++;
                updateChart(); // Call updateChart to refresh with new data
            }
        }

        // Handle tab click events to show/hide charts
        document.getElementById('student-login-tab').addEventListener('click', function() {
            document.getElementById('studentloginChart').style.display = 'block';
            document.getElementById('teacherloginChart').style.display = 'none';
            document.getElementById('chart-buttons').style.display = 'flex';
            // Update aria-selected attributes
            document.getElementById('student-login-tab').setAttribute('aria-selected', 'true');
            document.getElementById('teacher-login-tab').setAttribute('aria-selected', 'false');

            // Add and remove active class
            document.getElementById('student-login-tab').classList.add('active');
            document.getElementById('teacher-login-tab').classList.remove('active');
        });

        document.getElementById('teacher-login-tab').addEventListener('click', function() {
            document.getElementById('teacherloginChart').style.display = 'block';
            document.getElementById('studentloginChart').style.display = 'none';
            document.getElementById('chart-buttons').style.display = 'none';
            // Update aria-selected attributes
            document.getElementById('teacher-login-tab').setAttribute('aria-selected', 'true');
            document.getElementById('student-login-tab').setAttribute('aria-selected', 'false');
            // Add and remove active class
            document.getElementById('teacher-login-tab').classList.add('active');
            document.getElementById('student-login-tab').classList.remove('active');

        });

        // By default, show the student chart and hide the teacher chart
        document.getElementById('studentloginChart').style.display = 'block';
        document.getElementById('teacherloginChart').style.display = 'none';
    });
</script>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@section('page_js')
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
        title: 'Error!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif
@endsection