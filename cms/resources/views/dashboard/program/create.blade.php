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
                                        <div class="modal-content"><a href="#" class="close" data-bs-dismiss="modal">
                                                <em class="icon ni ni-cross-sm"></em></a>
                                            @if (session('success'))
                                                <div class="alert alert-success">
                                                    {{ session('success') }}
                                                </div>
                                            @endif

                                            <div class="modal-body modal-body-md">
                                                <h5 class="title">Add Cluster</h5>

                                                <form action="{{ route('programs.store') }}" method="POST"
                                                    enctype="multipart/form-data" class="tab-content">
                                                    @csrf
                                                    <div class="tab-pane active" id="student-info">
                                                        <div class="row gy-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="full-name">
                                                                        Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="full-name" placeholder="Cluster name"
                                                                        name="name" required>
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
                                                                            data-placeholder="Select multiple options"
                                                                            name="school_id" required>
                                                                            <option value="0" selected disabled>Select
                                                                                School</option>

                                                                            @foreach ($schools as $school)
                                                                                <option value="{{ $school->id }}">
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
                                                                            name="course_id[]"
                                                                            data-placeholder="Select multiple options"
                                                                            required multiple>
                                                                            

                                                                            @foreach ($courses as $course)
                                                                                <option value="{{ $course->id }}">
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
                                                                            name="stage_id"
                                                                            data-placeholder="Select multiple options"
                                                                            required>
                                                                            <option value="0" selected disabled>Select
                                                                                Stage</option>
                                                                            @foreach ($stages as $stage)
                                                                                <option value="{{ $stage->id }}">
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
                                                                            class="btn btn-primary">Create</button></li>
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
