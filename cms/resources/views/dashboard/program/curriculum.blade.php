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
                                        <h5 class="title">Add Curriculum</h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="{{ route('add-curriculum', $school->id) }}" method="POST" enctype="multipart/form-data" class="tab-content">
                                            @csrf
                                            <div class="row gy-4">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label class="form-label">Select Programs</label>
                                                        <div class="form-control-wrap">
                                                            <select class="form-select js-select2" multiple
                                                                name="program_id[]" id="program-select" required>
                                                                @foreach ($program as $programm)
                                                                <option value="{{ $programm->id }}">
                                                                    {{ $programm->name }}
                                                                    {{ \App\Models\Course::find($programm->course_id) ? \App\Models\Course::find($programm->course_id)->name : '-' }}
                                                                    /
                                                                    {{\App\Models\Stage::join('programs','stages.id','programs.stage_id')->where('programs.id',$programm->id)->select('stages.*')->first()->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>
                                                            @error('program_id')
                                                            <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                        <li>
                                                            <button type="submit" class="btn btn-primary">Add</button>
                                                        </li>
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
        $('#program-select').change(function() {
            let programIds = $(this).val();

            // if (programIds.length > 0) {
            $.ajax({
                url: '{{ route("get.units.by.program") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    program_id: programIds
                },
                success: function(response) {
                    $('#unit-select').empty();

                    $.each(response.units, function(key, unit) {
                        $('#unit-select').append('<option value="' + unit
                            .id + '">' + unit.name + '</option>');
                    });
                }
            });
            // } else {
            //     $('#unit-select').empty();
            // }
        });



        $('#unit-select').change(function() {
            let unitIds = $(this).val();

            if (unitIds.length > 0) {
                $.ajax({
                    url: '{{ route("get.lessons.by.units") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        unit_ids: unitIds
                    },
                    success: function(response) {
                        $('#lesson-select').empty();

                        $.each(response.lessons, function(key, lesson) {
                            $('#lesson-select').append('<option value="' + lesson
                                .id + '">' + lesson.name + '</option>');
                        });
                    }
                });
            } else {
                $('#lesson-select').empty();
            }
        });

    });
</script>
@endsection