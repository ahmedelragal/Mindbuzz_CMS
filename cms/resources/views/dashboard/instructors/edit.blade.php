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
                                    <div class="modal-content"><a href="#" class="close"
                                            data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Edit Teacher</h5>

                                            <form method="POST"
                                                action="{{ route('instructors.update', $instructor->id) }}"
                                                enctype="multipart/form-data">
                                                @csrf
                                                @method('put')
                                                <div class="tab-content">
                                                    <div class="tab-pane active" id="student-info">
                                                        <div class="row gy-4">
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="full-name">Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="full-name" placeholder="First name"
                                                                        name="name" value="{{ $instructor->name }}">

                                                                    @error('name')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="email">Email Address</label><input
                                                                        type="email" class="form-control"
                                                                        id="email" name="email"
                                                                        placeholder="Email Address"
                                                                        value="{{ $instructor->email }}">
                                                                    @error('email')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="phone-no">Phone Number</label>
                                                                    <span>(Optional)</span>

                                                                    <input type="text" class="form-control"
                                                                        id="phone-no" placeholder="Phone Number"
                                                                        name="phone" value="{{ $instructor->phone }}">
                                                                    @error('phone')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="password">Password</label>
                                                                    <input type="password" class="form-control"
                                                                        id="password" placeholder="Password"
                                                                        name="password">
                                                                    @error('password')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="confirm-password">Confirm Password</label>
                                                                    <input type="password" class="form-control"
                                                                        id="confirm-password"
                                                                        placeholder="Confirm Password"
                                                                        name="password_confirmation">
                                                                    @error('password_confirmation')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <div class="form-group"><label class="form-label"
                                                                        for="profile-picture">Profile Picture</label>
                                                                    <span>(Optional)</span>
                                                                    <br>
                                                                    <input type="file" id="profile-picture"
                                                                        name="parent_image">
                                                                    @error('parent_image')
                                                                    <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12">
                                                                <ul
                                                                    class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                                                    <li><button type="submit"
                                                                            class="btn btn-primary">Edit</button>
                                                                    </li>

                                                                </ul>
                                                            </div>
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
        $('#school_id').change(function() {
            $('#stage_id').prop('disabled', false)
        });
        $('#stage_id').change(function() {
            $('#program_id').prop('disabled', false)
            var stageId = $(this).val();
            var schoolId = $('#school_id').val();
            console.log(stageId);
            if (stageId) {
                $.ajax({
                    url: '/cms/public/get-courses/' + stageId + '/' + schoolId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log(data)
                        $('#program_id').empty();
                        $.each(data, function(key, value) {
                            $('#program_id').append('<option value="' + value.id +
                                '">' + value.course.name + '</option>');
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                    }
                });
            } else {
                $('#program_id').empty();
            }
        });
        $('.js-select2').select2();

        $('select[name="program_id[]"]').change(function() {
            $('#class_id').prop('disabled', false)

            var programId = $(this).val();
            var stageId = $('#stage_id').val();

            if (programId) {
                $.ajax({
                    url: '/cms/public/get-groups/' + programId + '/' + stageId,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $('select[name="group_id"]').empty();
                        $('select[name="group_id"]').append(
                            '<option value="">Select a class</option>');
                        $.each(data, function(key, value) {
                            $('select[name="group_id"]').append('<option value="' +
                                value.id + '">' + value.sec_name + '</option>');
                        });
                    }
                });
            } else {
                $('select[name="group_id"]').empty();
            }
        });
    });
</script>
@endsection