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
                            @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            @endif
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
                                            <h5 class="title">Edit School</h5>

                                            <form action="{{ route('schools.update', $schools->school_id) }}"
                                                method="POST" enctype="multipart/form-data" class="tab-content">
                                                @csrf
                                                @method('PUT')
                                                <div class="tab-pane active" id="student-info">
                                                    <div class="row gy-4">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="full-name">Name</label>
                                                                <input type="text" class="form-control"
                                                                    id="full-name" placeholder="First name"
                                                                    name="name" value="{{ $school->name }}">
                                                                @error('name')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="email">Email
                                                                    Address</label>
                                                                <input type="email" class="form-control"
                                                                    id="email" placeholder="Email Address"
                                                                    name="email" value="{{ $schools->email }}">
                                                                @error('email')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label" for="phone-no">Phone
                                                                    Number</label>
                                                                <input type="text" class="form-control"
                                                                    id="phone-no" placeholder="Phone Number"
                                                                    name="phone" value="{{ $schools->phone }}">
                                                                @error('phone')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label">Type</label>
                                                                <div class="form-control-wrap">
                                                                    <select class="form-select js-select2"
                                                                        data-placeholder="Select multiple options"
                                                                        name="type">
                                                                        <option value="national"
                                                                            {{ $school->type == 'national' ? 'selected' : '' }}>
                                                                            National</option>
                                                                        <option value="international"
                                                                            {{ $school->type == 'international' ? 'selected' : '' }}>
                                                                            International</option>
                                                                    </select>
                                                                    @error('type')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label class="form-label"
                                                                    for="password">Password</label>
                                                                <input type="password" class="form-control"
                                                                    id="password" name="password"
                                                                    placeholder="Password">
                                                                @error('password')
                                                                <div class="text-danger">{{ $message }}</div>
                                                                @enderror
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