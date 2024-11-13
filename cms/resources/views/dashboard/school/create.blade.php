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
                        <div class="nk-content-body d-flex justify-content-sm-center">
                            <div class=" card" role="dialog" style="width:60%;">
                                <div class="card-header">
                                    <h5 class="title">Add School</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('schools.store') }}" method="POST"
                                        enctype="multipart/form-data" class="tab-content">
                                        @csrf
                                        <div class="mb-3">
                                            <div class="form-group"><label class="form-label"
                                                    for="full-name">
                                                    Name</label><input type="text"
                                                    class="form-control" id="full-name"
                                                    placeholder="Enter School name" name="name" required>
                                                @error('name')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-group"><label class="form-label"
                                                    for="email">Email</label><input type="email"
                                                    class="form-control" id="email"
                                                    placeholder="Enter School Email" name="email" required>
                                                @error('email')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group"><label class="form-label"
                                                        for="phone-no">Phone</label>
                                                    <input type="text" class="form-control"
                                                        placeholder="Enter School Phone Number"
                                                        name="phone" required>
                                                    @error('phone')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group"><label
                                                        class="form-label">Type</label>
                                                    <div class="form-control-wrap">
                                                        <select class="form-select js-select2"
                                                            data-placeholder="Select School Type"
                                                            name="type" required>
                                                            <option value="" disabled selected>Select School Type</option>
                                                            <option value="national">National</option>
                                                            <option value="international">International
                                                            </option>
                                                        </select>
                                                        @error('type')
                                                        <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="password">Password</label>
                                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter Password" required>
                                                    @error('password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                                        placeholder="Confirm Password" required>
                                                    @error('password_confirmation')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-12">
                                            <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                <li><button type="submit"
                                                        class="btn btn-primary mt-3 mb-3">Add School</button></li>
                                            </ul>
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