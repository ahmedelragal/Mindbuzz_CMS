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
                                        <h3 class="nk-block-title page-title">Dashboard</h3>
                                        <div class="nk-block-des text-soft">
                                            <p>Welcome to Your Dashboard.</p>
                                        </div>
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
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-inner">
                                                <div class="card-title text-center">
                                                    <h4 class="title">Total Users</h4>
                                                    <h2 class="fs-2 text-dark">{{ $totalUsers }}</h2>
                                                </div>
                                                <div>
                                                    <canvas id="usersChart"
                                                        style="display: block; box-sizing: border-box; height: 500px; width: 500px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if (!is_null($totalSchools))
                                    <div class="col-md-6">
                                        <div class="card">
                                            <div class="card-inner">
                                                <div class="card-title text-center">
                                                    <h4 class="title">Total Schools</h4>
                                                    <h2 class="fs-2 text-dark">{{ $totalSchools }}</h2>
                                                </div>
                                                <div>
                                                    <canvas id="schoolsChart"
                                                        style="display: block; box-sizing: border-box; height: 500px; width: 500px;"></canvas>
                                                </div>
                                            </div>
                                        </div>
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
                backgroundColor: ['#4CAF50', '#FF9800'],
                hoverBackgroundColor: ['#66BB6A', '#FFB74D'],
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
                backgroundColor: ['#1E88E5', '#E53935'],
                hoverBackgroundColor: ['#42A5F5', '#EF5350'],
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