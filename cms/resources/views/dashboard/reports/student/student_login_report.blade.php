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
                                        <div class="row">
                                            <!-- School Filter -->
                                            <div class="col-md-6">
                                                @role('Admin')
                                                <label for="school_id">Select School</label>
                                                <select class="form-select js-select2" name="school_id" id="school_id" required>
                                                    <option value="" selected disabled>Choose a School</option>
                                                    @foreach ($schools as $school)
                                                    <option value="{{ $school->id }}" data-school="{{ $school->id }}" {{ old('school_id', $request['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                                                        {{ $school->name }}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @endrole

                                                @if(auth()->user()->hasRole('school') || auth()->user()->hasRole('Cordinator'))
                                                <input type="hidden" name="school_id" id="school_id" value="{{ auth()->user()->school_id }}">
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label for="student_id">Select Student</label>
                                                <select class="form-select js-select2" name="student_id" id="student_id" required>
                                                    @role('Admin')
                                                    <option value="" selected disabled>Choose a Student</option>
                                                    @endrole
                                                    @if(auth()->user()->hasRole('school') || auth()->user()->hasRole('Cordinator'))
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
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-row mt-4">
                                            <div class="col-md-12 text-right">
                                                <button type="submit" class="btn btn-primary">View Report</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @if(isset($studentName) && isset($numLogin))
                            <div class="card mt-4">
                                <section id="report_container" style="display: block;">
                                    <div class="card-body">
                                        <!-- Display Circle with Default Number -->
                                        <div class="d-flex justify-content-center align-items-center mt-4" style="font-size:32px;color: #d17e00; font-weight:bold">Number of Logins</div>
                                        <div class="container d-flex justify-content-center align-items-center" style="height: 350px;">
                                            <!-- Circle Figure -->
                                            <div id="circle"
                                                class="d-flex justify-content-center align-items-center"
                                                style="width: 250px; height: 250px; border-radius: 50%; background-color: #f5f5f5; border: 4px solid #d17e00; font-size: 92px; color: #d17e00; font-weight:500">
                                                {{$numLogin}}
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
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

@section('page_js')
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
@role('Admin')
<script>
    $(document).ready(function() {
        // Initialize select2 for the filters
        $('.js-select2').select2();
        var selectedStudentId = "{{$request['student_id'] ?? '' }}";

        $('#school_id').change(function() {
            var schoolId = $('#school_id option:selected').data('school');
            getSchoolStudents(schoolId, selectedStudentId);
        });
        $('#school_id').trigger('change');

    });

    function getSchoolStudents(schoolId, selectedStudentId) {
        $.ajax({
            url: '/get-students-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                // Clear the existing options
                $('select[name="student_id"]').empty();
                if (!data || data.length === 0) {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>No Available Students</option>'
                    );
                } else {
                    $('select[name="student_id"]').append(
                        '<option value="" selected disabled>Choose a Student</option>'
                    );
                    $.each(data, function(key, value) {
                        $('select[name="student_id"]').append(
                            '<option value="' + value.id + '">' + value.name + '</option>'
                        );
                    });
                    if (selectedStudentId) {
                        $('select[name="student_id"]').val(selectedStudentId).trigger('change');
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>
@endrole
@endsection