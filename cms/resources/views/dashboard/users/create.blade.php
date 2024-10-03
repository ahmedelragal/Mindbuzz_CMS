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
                                        <a href="#" class="close" data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Add User</h5>
                                            <form action="{{ route('users.store') }}" method="POST">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="name" name="name" required>
                                                    @error('name')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" required>
                                                    @error('email')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Password</label>
                                                    <input type="password" class="form-control" id="password" name="password" required>
                                                    @error('password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="mb-3">
                                                    <label for="roles" class="form-label">Assign Roles</label>
                                                    <select name="roles" id="roles" class="form-select" required>
                                                        @foreach ($roles as $role)
                                                        @if ($role->name !== 'school')
                                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                                        @endif
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                                    @error('phone')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <!-- Additional fields to be shown/hidden based on selected role -->
                                                <div id="schoolFields">
                                                    <div class="mb-3">
                                                        <label for="schoolType" class="form-label">Type of School</label>
                                                        <select name="type" id="schoolType" class="form-select">
                                                            <option value="" disabled selected>Select Type</option>
                                                            <option value="international">International</option>
                                                            <option value="national">National</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Create User</button>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.getElementById('roles');
        const schoolFields = document.getElementById('schoolFields');
        const phoneField = document.getElementById('phone');
        const schoolTypeField = document.getElementById('schoolType');

        // Function to toggle visibility of the additional fields based on selected role
        function toggleSchoolFields() {
            if (roleSelect.value === 'school') {
                schoolFields.style.display = 'block';
                schoolTypeField.setAttribute('required', 'required');
            } else {
                schoolFields.style.display = 'none';
                schoolTypeField.removeAttribute('required');
            }
        }

        // Initial check on page load
        toggleSchoolFields();

        // Listen for changes in the roles dropdown
        roleSelect.addEventListener('change', toggleSchoolFields);
    });
</script>
@endsection