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
                            <div class=" card" role="dialog" id="student-add" style="width:60%;">
                                <div class="card-header">
                                    <h5 class="title">Add User</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('users.store') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name"
                                                placeholder="Enter Username" value="{{ old('name') }}" required>
                                            @error('name')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                placeholder="Enter User Email" value="{{ old('email') }}" required>
                                            @error('email')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone"
                                                placeholder="Enter User Phone Number" value="{{ old('phone') }}" required>
                                            @error('phone')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="password" class="form-label">Password</label>
                                                <input type="password" class="form-control" id="password" name="password"
                                                    placeholder="Enter Password" required>
                                                @error('password')
                                                <div class="text-danger">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="form-label" for="confirm-password">Confirm Password</label>
                                                    <input type="password" class="form-control" id="confirm-password"
                                                        placeholder="Confirm Password" name="password_confirmation">
                                                    @error('password_confirmation')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="roles" class="form-label">Assign Roles</label>
                                            <div>
                                                <!-- Display checkboxes for roles -->
                                                @foreach ($roles as $role)
                                                @if ($role->name !== 'Cordinator')
                                                <div>
                                                    <label>
                                                        <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="role-checkbox"
                                                            {{ is_array(old('roles')) && in_array($role->name, old('roles')) ? 'checked' : '' }}>
                                                        @if ($role->name == 'school')
                                                        School Admin
                                                        @elseif ($role->name == 'Admin')
                                                        Super Admin
                                                        @else
                                                        {{ $role->name }}
                                                        @endif
                                                    </label>
                                                </div>
                                                @endif
                                                @endforeach
                                            </div>

                                            <!-- Display error message if validation fails -->
                                            @error('roles')
                                            <div class="text-danger">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="school-selection" style="display: none; margin-top: 10px; margin-bottom: 10px;">
                                            <label for="school_id">Select School <span style="color: red;">*</span></label>
                                            <select name="school_id" id="school_id" class="form-select">
                                                <option value="" disabled>Select a school</option>
                                                @foreach ($schools as $school)
                                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                                    {{ $school->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-3 mb-3">Add User</button>
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
@section('page_js')
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const roleCheckboxes = document.querySelectorAll('.role-checkbox');
        const schoolSelection = document.getElementById('school-selection');

        function toggleSchoolField() {
            let showSchoolField = false;

            // Check if 'school' or 'Cordinator' role is selected
            roleCheckboxes.forEach(checkbox => {
                if ((checkbox.value === 'school' || checkbox.value === 'Culture-Cordinator' || checkbox.value === 'PracticalLife-Cordinator' || checkbox.value === 'Math-Cordinator' || checkbox.value === 'Arabic-Cordinator' || checkbox.value === 'Phonics-Cordinator') && checkbox.checked) {
                    showSchoolField = true;
                }
            });

            // Show or hide the school selection field
            if (showSchoolField) {
                schoolSelection.style.display = 'block';
                document.getElementById('school_id').setAttribute('required', 'required');
            } else {
                schoolSelection.style.display = 'none';
                document.getElementById('school_id').removeAttribute('required');
            }
        }

        // Add event listener to checkboxes
        roleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', toggleSchoolField);
        });

        // Run on page load to handle pre-checked roles
        toggleSchoolField();
    });
</script>
@if(session('error'))
<script>
    Swal.fire({
        title: 'Error!',
        text: @json(session('error')),
        icon: 'error',
        confirmButtonText: 'Ok'
    });
</script>
@endif
@endsection