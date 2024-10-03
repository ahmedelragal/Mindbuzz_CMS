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
                                <div class="" role="dialog" id="program-edit">
                                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                        <div class="modal-content"><a href="#" class="close" data-bs-dismiss="modal">
                                                <em class="icon ni ni-cross-sm"></em></a>
                                            @if (session('success'))
                                                <div class="alert alert-success">
                                                    {{ session('success') }}
                                                </div>
                                            @endif
                                            <div class="modal-body modal-body-md">
                                                <h5 class="title">Edit Cluster</h5>
                                                <form action="{{ route('programs.update', $program->id) }}" method="POST"
                                                    enctype="multipart/form-data" class="tab-content">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="tab-pane active" id="program-info">
                                                        <div class="row gy-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="full-name">
                                                                        Name</label><input type="text"
                                                                        class="form-control" id="full-name"
                                                                        placeholder="Program name" name="name"
                                                                        value="{{ $program->name }}" required>
                                                                    @error('name')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group"><label
                                                                        class="form-label">School</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select js-select2"
                                                                            name="school_id" required>
                                                                            @foreach ($schools as $school)
                                                                                <option value="{{ $school->id }}"
                                                                                    {{ $program->school_id == $school->id ? 'selected' : '' }}>
                                                                                    {{ $school->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('school_id')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group"><label
                                                                        class="form-label">Course</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select js-select2"
                                                                            name="course_id[]" multiple required>
                                                                            @foreach ($courses as $course)
                                                                                <option value="{{ $course->id }}"
                                                                                    {{ $program->course_id == $course->id ? 'selected' : '' }}>
                                                                                    {{ $course->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('course_id')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group"><label
                                                                        class="form-label">Stage</label>
                                                                    <div class="form-control-wrap">
                                                                        <select class="form-select js-select2"
                                                                            name="stage_id" required>
                                                                            @foreach ($stages as $stage)
                                                                                <option value="{{ $stage->id }}"
                                                                                    {{ $program->stage_id == $stage->id ? 'selected' : '' }}>
                                                                                    {{ $stage->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('stage_id')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-12">
                                                                <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                    <li><button type="submit"
                                                                            class="btn btn-primary">Update</button></li>
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
