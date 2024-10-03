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
                            <div role="dialog" id="student-add">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <a href="#" class="close" data-bs-dismiss="modal">
                                            <em class="icon ni ni-cross-sm"></em>
                                        </a>
                                        @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                        @endif
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Add Class</h5>
                                            <form action="{{ route('classes.store') }}" method="POST"
                                                class="tab-content">
                                                @csrf
                                                <div class="tab-pane active" id="student-info">
                                                    <div class="row gy-4">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" name="name"
                                                                    placeholder="Class name">
                                                                @error('name')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <!-- <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Sec
                                                                    Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" name="sec_name"
                                                                    placeholder="Second name">
                                                                @error('sec_name')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div> -->
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                @role('Admin')
                                                                <label class="form-label">School</label>
                                                                <div class="form-control-wrap">
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
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-2">
                                                                <label class="form-label">Programs</label>
                                                                <div class="form-control-wrap">
                                                                    @role('Admin')
                                                                    <select class="form-select js-select2" id="program_ids" name="program_id[]" multiple required>
                                                                    </select>
                                                                    @endrole
                                                                    @role('school')
                                                                    <select class="form-select js-select2" id="" name="program_id[]" multiple required>
                                                                        @foreach ($programs as $program)
                                                                        <option value="{{$program->id}}">{{$program->course->name . ' - '.$program->stage->name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    @endrole
                                                                    @error('program_id')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6" style="display:none;">
                                                            <div class="form-group">
                                                                <label class="form-label" for="stage-name">Stage</label>
                                                                <input type="text" class="form-control" id="stage-name" disabled>
                                                                <input type="hidden" id="stage-id" name="stage_id">
                                                                @error('stage_id')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>


                                                        <div class="col-md-12">
                                                            <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                <li>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Create</button>
                                                                </li>
                                                            </ul>
                                                        </div>
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
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection

@section('page_js')
<script>
    $(document).ready(function() {
        $('#program_id').change(function() {
            var programId = $(this).val();
            console.log(programId);
            if (programId) {
                $.ajax({
                    url: '/cms/public/get-stages/' + programId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#stage-name').val(data.name);
                        $('#stage-id').val(data
                            .id); // Set the hidden input with the stage_id
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#stage-name').val('');
                        $('#stage-id').val(''); // Clear the hidden input
                    }
                });
            } else {
                $('#stage-name').val('');
                $('#stage-id').val(''); // Clear the hidden input
            }
        });


        $('#school_id').change(function() {
            var schoolId = $('#school_id').val();
            getProgramsBySchool(schoolId)
        });

    });
    var currSchool = $('#curr-school').val();

    function getProgramsBySchool(schoolId) {
        $.ajax({
            url: '/get-programs-school/' + schoolId,
            type: "GET",
            dataType: "json",
            success: function(data) {
                console.log(data);
                $('select[name="program_id[]"]').empty();
                $('select[name="program_id[]"]').append(
                    '<option value="">Select a Program</option>');
                $.each(data, function(key, value) {
                    $('select[name="program_id[]"]').append('<option value="' +
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