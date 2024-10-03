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
                            <div class="" role="dialog" id="student-add">
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
                                            <h5 class="title">Edit Class</h5>
                                            <form action="{{ route('classes.update', $class->id) }}" method="POST"
                                                class="tab-content">
                                                @csrf
                                                @method('PUT')
                                                <div class="tab-pane active" id="student-info">
                                                    <div class="row gy-4">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" name="name"
                                                                    placeholder="Class name"
                                                                    value="{{ $class->name }}">
                                                            </div>
                                                        </div>
                                                        <!-- <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Sec
                                                                    Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" name="sec_name"
                                                                    placeholder="Second name"
                                                                    value="{{ $class->sec_name }}">
                                                            </div>
                                                        </div> -->


                                                        <!-- <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Program</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2"
                                                                        id="program_id"
                                                                        data-placeholder="Select multiple options"
                                                                        name="program_id" required>
                                                                        <option value="0" selected disabled>Select
                                                                            Program</option>
                                                                        @foreach ($programs as $program)
                                                                        @foreach ($programs as $program)
                                                                        <option value="{{ $program->id }}"
                                                                            {{ $class->program_id == $program->id ? 'selected' : '' }}>
                                                                            {{ $program->name ?? '-' }} /
                                                                            {{ $program->course->name ?? '-' }}
                                                                            /
                                                                            {{ $program->stage->name ?? '-' }}

                                                                        </option>
                                                                        @endforeach
                                                                        {{-- <option value="{{ $program->id }}"
                                                                        {{ $class->program_id == $program->id ? 'selected' : '' }}>
                                                                        {{ $program->name . '/' . $program->course->name }}
                                                                        </option> --}}
                                                                        @endforeach
                                                                    </select>
                                                                    @error('program_id')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div> -->
                                                        <div class="col-md-6" style="display:none;">
                                                            <div class="form-group">
                                                                <label class="form-label" for="stage-name">Stage</label>
                                                                <input type="text" class="form-control"
                                                                    id="stage-name" disabled>
                                                                <input type="hidden"
                                                                    value="{{ $class->stage_id }}" id="stage-id"
                                                                    name="stage_id">
                                                                @error('stage_id')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        {{-- <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="stage-name">Stage</label>
                                                                    <input type="text" class="form-control"
                                                                        id="stage-name" disabled>
                                                                    <input type="hidden" id="stage-id" name="stage_id"
                                                                        value="{{ $class->stage_id }}">
                                                        @error('stage_id')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div> --}}

                                                <div class="col-md-12">
                                                    <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                        <li>
                                                            <button type="submit"
                                                                class="btn btn-primary">Update</button>
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
                            .id);
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        $('#stage-name').val('');
                        $('#stage-id').val('');
                    }
                });
            } else {
                $('#stage-name').val('');
                $('#stage-id').val('');
            }
        });

        if ($('#program_id').val()) {
            $('#program_id').trigger('change');
        }
    });
</script>
@endsection