@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap ">
            @include('dashboard.layouts.navbar')
            <div class="nk-content ">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        @role('Admin')
                                        <h3 class="nk-block-title page-title">Super Admin Dashboard</h3>
                                        @endrole
                                        @role('school')
                                        <h3 class="nk-block-title page-title">School Admin Dashboard</h3>
                                        @endrole
                                        <!-- <div class="nk-block-des text-soft">
                                            <p>Welcome to Your Dashboard.</p>
                                        </div> -->
                                    </div>
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle">
                                            <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                data-target="pageMenu">
                                                <em class="icon ni ni-more-v"></em>
                                            </a>
                                            <!-- <div class="toggle-expand-content" data-content="pageMenu">
                                                <ul class="nk-block-tools g-3">
                                                    <li class="nk-block-tools-opt">
                                                        <a href="{{ route('reports.index') }}" class="btn btn-primary">
                                                            <em class="icon ni ni-reports"></em><span>Reports</span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nk-block">
                                <div class="row g-gs">
                                    @role('Admin')
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-inner" style="padding-top:0">
                                                <div class="card-title text-center">
                                                    <h4 class="title">Total Users</h4>
                                                    <h2 class="fs-2 text-dark">{{ $totalUsers }}</h2>
                                                </div>
                                                <div>
                                                    <canvas id="usersChart"
                                                        style="display: block; box-sizing: border-box; height: 450px; width: 500px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endrole
                                    @role('school')
                                    <div class="">
                                        <div class="card">
                                            <div class="card-inner" style="padding-top:0">
                                                <div class="card-title text-center">
                                                    <h4 class="title" style="font-size: 1.2rem;">Total Users</h4>
                                                    <h2 class="fs-2 text-dark">{{ $totalUsers }}</h2>
                                                </div>
                                                <div>
                                                    <canvas id="usersChart"
                                                        style="display: block; box-sizing: border-box; height: 450px; width: 500px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endrole
                                    @if (!is_null($totalSchools))
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-inner" style="padding-top:0">
                                                <div class="card-title text-center">
                                                    <h4 class="title">Total Schools</h4>
                                                    <h2 class="fs-2 text-dark">{{ $totalSchools }}</h2>
                                                </div>
                                                <div>
                                                    <canvas id="schoolsChart"
                                                        style="display: block; box-sizing: border-box; height: 450px; width: 500px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-4">
                                        <div class="card mb-3">
                                            <div class="card-header text-white" style="font-size: 1.0rem;font-weight: 700;">Total Programs</div>
                                            <div class="card-body" style="background-color: white; height:95px; max-height:95px;">
                                                <h6 class="card-title">Count</h6>
                                                <p class="card-text">{{ $totalPrograms }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card mb-3">
                                            <div class="card-header text-white" style="font-size: 1.0rem;font-weight: 700;">Total Classes</div>
                                            <div class="card-body" style="background-color: white; height:95px; max-height:95px;">
                                                <h6 class="card-title">Count</h6>
                                                <p class="card-text">{{ $totalClasses }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card mb-3">
                                            <div class="card-header text-white" style="font-size: 1.0rem;font-weight: 700;">Student Gender Percentage</div>
                                            <div class="card-body" style="background-color: white; height:95px; max-height:95px;display: flex;align-items: center;">

                                                <h6 class="card-text" style="font-size:16.8px; color:#526484;">
                                                    <p>Boys: {{$boyPercentage}}%</p>
                                                    <p style="font-size:16.8px;">Girls: {{$girlPercentage}}%</p>

                                                </h6>
                                            </div>
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
<input id="studentsInSchool" type="hidden" value="{{$studentsInSchool}}">
<input id="teachersInSchool" type="hidden" value="{{$teachersInSchool}}">
<input id="nationalSchools" type="hidden" value="{{$nationalSchools}}">
<input id="internationalSchools" type="hidden" value="{{$internationalSchools}}">
@section('page_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.min.js"
    integrity="sha512-L0Shl7nXXzIlBSUUPpxrokqq4ojqgZFQczTYlGjzONGTDAcLremjwaWv5A+EDLnxhQzY5xUZPWLOLqYRkY0Cbw=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    var studentsInSchool = document.getElementById('studentsInSchool').value;
    var teachersInSchool = document.getElementById('teachersInSchool').value;
    var nationalSchools = document.getElementById('nationalSchools').value;
    var internationalSchools = document.getElementById('internationalSchools').value;

    var ctxUsers = document.getElementById('usersChart').getContext('2d');
    var usersChart = new Chart(ctxUsers, {
        type: 'doughnut',
        data: {
            labels: ['Students', 'Teachers'],
            datasets: [{
                label: 'Total Users',
                data: [studentsInSchool, teachersInSchool],
                backgroundColor: ['#66BB6A', '#FF9800'],
                hoverBackgroundColor: ['#d17e00', '#FFB74D'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: function(evt, item) {
                if (item.length) {
                    var index = item[0].index;
                    if (index === 0) {
                        window.location.href = "{{ route('students.index') }}";
                    } else if (index === 1) {
                        window.location.href = "{{ route('instructors.index') }}";
                    }
                }
            }
        }
    });

    var ctxSchools = document.getElementById('schoolsChart').getContext('2d');
    var schoolsChart = new Chart(ctxSchools, {
        type: 'doughnut',
        data: {
            labels: ['National Schools', 'International Schools'],
            datasets: [{
                label: 'Total Schools',
                data: [nationalSchools, internationalSchools],
                backgroundColor: ['#66BB6A', '#FF9800'],
                hoverBackgroundColor: ['#d17e00', '#FFB74D'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: function(evt, item) {
                if (item.length) {
                    var index = item[0].index;
                    if (index === 0) {
                        window.location.href = "{{ route('schools.index') }}";
                    } else if (index === 1) {
                        window.location.href = "{{ route('schools.index') }}";
                    }
                }
            }
        }
    });
</script>
@endsection