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
                                    <div class="modal-content"><a href="#" class="close"
                                            data-bs-dismiss="modal"><em class="icon ni ni-cross-sm"></em></a>
                                        <div class="modal-body modal-body-md">
                                            <h5 class="title">Edit user</h5>

                                            <form action="{{ route('users.update', $user->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Name</label>
                                                    <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone" value="{{ $user->phone }}" name="phone">
                                                    @error('phone')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password" class="form-label">Password (Leave blank to keep current password)</label>
                                                    <input type="password" class="form-control" id="password" name="password">
                                                    @error('password')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                                    @error('password_confirmation')
                                                    <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="mb-3">
                                                    <label for="roles" class="form-label">Assign Roles</label>
                                                    <div>
                                                        <!-- Display checkboxes for roles -->
                                                        @foreach ($roles as $role)
                                                        @if ($role->name !== 'Cordinator')
                                                        <div>
                                                            <label>
                                                                <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                                                                    {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                                                    class="role-checkbox">
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
                                                        <option value="" disabled {{ $user->school_id === null ? 'selected' : '' }}>Select a school</option>
                                                        @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}" {{ $user->school_id == $school->id ? 'selected' : '' }}>
                                                            {{ $school->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Update User</button>
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