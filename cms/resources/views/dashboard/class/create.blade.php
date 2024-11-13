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
                            <div class="nk-content-body d-flex justify-content-sm-center">
                                <div class=" card" role="dialog" style="width:60%;">
                                    <div class="card-header">
                                        <h5 class="title">Add Class</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('classes.store') }}" method="POST" class="tab-content">
                                            @csrf

                                            <div class="mb-3">

                                                <div class="form-group">
                                                    <label class="form-label" for="full-name">Name</label>
                                                    <input type="text" class="form-control"
                                                        id="full-name" name="name"
                                                        placeholder="Enter Class Name">
                                                    @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror

                                                </div>

                                                <div class="mb-3">
                                                    @role('Admin')
                                                    <label class="form-label">School</label>
                                                    <select class="form-select js-select2"
                                                        data-placeholder="Select multiple options"
                                                        name="school_id" id="school_id" required>
                                                        <option value="0" selected disabled>Select
                                                            School</option>
                                                        @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}">
                                                            {{ $school->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @endrole
                                                    @role('school')
                                                    <input type="hidden" name="school_id" id="school_id" value="{{$schools[0]->id}}">
                                                    @endrole
                                                    @error('school_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Programs</label>
                                                    <select class="form-select js-select2" id="program_ids" name="program_id[]" multiple required></select>
                                                    @error('program_id')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>


                                                <div class="col-md-12">
                                                    <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                        <li><button type="submit"
                                                                class="btn btn-primary mt-3">Add Class</button></li>
                                                    </ul>
                                                </div>
                                            </div>

                                        </form>
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

@section('page_js')
<script>
    $(document).ready(function() {
        $('.js-select2').select2();
        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getProgramsBySchool(schoolId)
        });
        $('#school_id').trigger('change');

    });

    function getProgramsBySchool(schoolId) {
        $.ajax({
            url: '/get-programs-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                $('select[name="program_id[]"]').empty();

                if (!data || data.length === 0) {
                    $('select[name="program_id"]').append(
                        '<option value="" selected disabled>No Available Programs</option>'
                    );
                } else {
                    $('select[name="program_id[]"]').append('<option value="" disabled>Select a Program</option>');
                    $.each(data, function(key, value) {
                        $('select[name="program_id[]"]').append('<option value="' +
                            value.id + '">' +
                            value.program_details + '</option>');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }
</script>


@endsection