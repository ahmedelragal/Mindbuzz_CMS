@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        <!-- Sidebar -->
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap ">
            <!-- Navbar -->
            @include('dashboard.layouts.navbar')

            <div class="nk-content ">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="title">Class Gender Engagement Report</h5>
                                </div>
                                <!-- Form Section -->

                                <div class="card-body">
                                    <form method="GET" action="{{ route('reports.classGenderReportWeb') }}">
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
                                                <select class="form-select js-select2" name="program_id" id="program_id">
                                                    <option value="" disabled selected>Choose a Program</option>
                                                </select>
                                            </div>

                                            <!-- Gender Filter -->
                                            <div class="col-md-4">
                                                <label for="gender">Select Gender</label>
                                                <select class="form-select js-select2" name="gender" id="filter" required>
                                                    <option value="" disabled {{ old('gender', $request['gender'] ?? '') == '' ? 'selected' : '' }}>Choose a Gender</option>
                                                    <option value="Boy" {{ old('gender', $request['gender'] ?? '') == 'Boy' ? 'selected' : '' }}>Boy</option>
                                                    <option value="Girl" {{ old('gender', $request['gender'] ?? '') == 'Girl' ? 'selected' : '' }}>Girl</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Additional Filters -->
                                        <div class="row mt-3">
                                            <!-- Filter By -->
                                            <div class="col-md-4">
                                                <label for="filter">Filter By</label>
                                                <!-- <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="" disabled selected>Choose a filter</option>
                                                    <option value="Unit">Unit</option>
                                                    <option value="Lesson">Lesson</option>
                                                    <option value="Game">Game</option>
                                                    <option value="Skill">Skill</option>
                                                </select> -->
                                                <select class="form-select js-select2" name="filter" id="filter">
                                                    <option value="" disabled {{ old('filter', $request['filter'] ?? '') == '' ? 'selected' : '' }}>Choose a filter</option>
                                                    <option value="Unit" {{ old('filter', $request['filter'] ?? '') == 'Unit' ? 'selected' : '' }}>Unit</option>
                                                    <option value="Lesson" {{ old('filter', $request['filter'] ?? '') == 'Lesson' ? 'selected' : '' }}>Lesson</option>
                                                    <option value="Game" {{ old('filter', $request['filter'] ?? '') == 'Game' ? 'selected' : '' }}>Game</option>
                                                    <option value="Skill" {{ old('filter', $request['filter'] ?? '') == 'Skill' ? 'selected' : '' }}>Skill</option>
                                                </select>
                                            </div>

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
                            @if (!empty($skills) || !empty($units) || !empty($lessons) || !empty($games))
                            <div class="card mt-4">
                                <div class="card-body">
                                    <h3>Report</h3>

                                    @if (!empty($units))
                                    <h5>Units Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Unit</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($units as $unit)
                                            <tr>
                                                <td>{{ $unit['name'] }}</td>
                                                <td>{{ $unit['failed'] }}</td>
                                                <td>{{ $unit['introduced'] }}</td>
                                                <td>{{ $unit['practiced'] }}</td>
                                                <td>{{ $unit['mastered'] }}</td>
                                                <td>{{ $unit['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($lessons))
                                    <h5>Lessons Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Lesson</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lessons as $lesson)
                                            <tr>
                                                <td>{{ $lesson['name'] }}</td>
                                                <td>{{ $lesson['failed'] }}</td>
                                                <td>{{ $lesson['introduced'] }}</td>
                                                <td>{{ $lesson['practiced'] }}</td>
                                                <td>{{ $lesson['mastered'] }}</td>
                                                <td>{{ $lesson['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($games))
                                    <h5>Games Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Game</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($games as $game)
                                            <tr>
                                                <td>{{ $game['name'] }}</td>
                                                <td>{{ $game['failed'] }}</td>
                                                <td>{{ $game['introduced'] }}</td>
                                                <td>{{ $game['practiced'] }}</td>
                                                <td>{{ $game['mastered'] }}</td>
                                                <td>{{ $game['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif

                                    @if (!empty($skills))
                                    <h5>Skills Engagement</h5>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Skill</th>
                                                <th>Failed</th>
                                                <th>Introduced</th>
                                                <th>Practiced</th>
                                                <th>Mastered</th>
                                                <th>Engagement Percentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($skills as $skill)
                                            <tr>
                                                <td>{{ $skill['name'] }}</td>
                                                <td>{{ $skill['failed'] }}</td>
                                                <td>{{ $skill['introduced'] }}</td>
                                                <td>{{ $skill['practiced'] }}</td>
                                                <td>{{ $skill['mastered'] }}</td>
                                                <td>{{ $skill['mastery_percentage'] }}%</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div style="margin-top: 20px; margin-left:10px;">
                                <p>No data available for the selected filters.</p>
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


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add event listeners to all buttons with a modal target
        document.querySelectorAll('[data-bs-target^="#viewTeacherPrograms"]').forEach(button => {
            button.addEventListener('click', function() {
                // Retrieve data from button attributes
                const teacherName = this.getAttribute('data-teacher-name');
                const programs = JSON.parse(this.getAttribute('data-programs'));
                const modalIndex = this.getAttribute('data-bs-target').split('-').pop();

                const tableBody = document.getElementById('programs-table-body-' + modalIndex);
                tableBody.innerHTML = ''; // Clear previous content

                // Populate modal with data
                programs.forEach(course => {
                    const row = document.createElement('tr');

                    // Create form with remove button
                    const formHtml = `
                        <form action="{{ route('teachers.remove', ':id') }}" method="POST"> 
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Remove</button>
                        </form>
                    `.replace(':id', course.id); // Replace placeholder with actual ID

                    row.innerHTML = `
                        <td>${course.coTeacher || 'None'}</td>
                        <td>${course.course}</td>
                        <td>${course.stage}</td>
                        <td>${formHtml}</td>
                    `;

                    tableBody.appendChild(row);
                });
            });
        });
    });
</script>

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
                $('select[name="program_id"]').append('<option value="">Choose a Program</option>');
                $.each(data, function(key, value) {
                    $('select[name="program_id"]').append('<option value="' + value.id + '">' + value.program_details + '</option>');
                });
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endsection