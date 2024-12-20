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
                            @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif
                            <div class="" role="dialog" id="student-add">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                        @endif
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Add Courses</h5>

                                            <form action="{{ route('courses.store') }}" method="POST"
                                                class="tab-content">
                                                @csrf
                                                <div class="tab-pane active" id="student-info">
                                                    <div class="row gy-4">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" name="name"
                                                                    placeholder="Course name">
                                                                @error('name')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>



                                                        <div class="col-md-12">
                                                            <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                <li>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">create</button>
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