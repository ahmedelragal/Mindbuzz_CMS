<!-- resources/views/dashboard/reports/class/select_group.blade.php -->

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
                                    <h5 class="title">Select class for reports</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('reports.classCompletionReportWeb') }}">
                                        @csrf
                                        <div class="form-row">
                                            <div class="col-md-6">
                                                <label for="group_id">Select school/class</label>
                                                <select class="form-select js-select2" name="group_id" id="group_id" required>
                                                    <option value="" disabled selected>Choose a school/class</option>
                                                    @foreach ($groups as $group)
                                                    @php
                                                    $sch = App\Models\School::where('id', $group->school_id)->first();
                                                    @endphp
                                                    <option value="{{ $group->id }}" data-school="{{ $sch->id }}">{{ $sch->name }} / {{ $group->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="submit" class="btn btn-primary mt-4">View
                                                    Reports</button>
                                            </div>
                                        </div>
                                    </form>
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