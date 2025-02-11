@php
session(['users_previous_url' => url()->full()]);
@endphp
@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap">
            @include('dashboard.layouts.navbar')
            <div class="nk-content min-vh-90">
                <div class="container-fluid">
                    <div class="nk-content-inner">
                        <div class="nk-content-body">
                            <div class="nk-block-head nk-block-head-sm">
                                <div>
                                    <div class="nk-block-head-content" style="display:flex; align-items:center; justify-content:space-between">
                                        <h3 class="nk-block-title page-title">Users List</h3>
                                        <div class="nk-block-head nk-block-head-sm" style="margin-top: 20px;">
                                            <div class="nk-block-head-content" style="display:flex; gap:10px;justify-content:space-between">
                                                <div class="toggle-wrap nk-block-tools-toggle">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                        data-target="more-options">
                                                        <em class="icon ni ni-more-v"></em>
                                                    </a>
                                                    <div class="toggle-expand-content " data-content="more-options">
                                                        <ul class="nk-block-tools d-flex justify-content-between" style="display:flex; gap:10px;">
                                                            <li>
                                                                <button type="button" onclick="massDelete()" class="btn btn-danger">Delete Selected</button>
                                                            </li>
                                                            <li class="nk-block-tools-opt"><a
                                                                    class="btn btn-icon btn-primary d-md-none"
                                                                    data-bs-toggle="modal" href="#student-add"><em
                                                                        class="icon ni ni-plus"></em></a>
                                                                <a href="{{ route('users.create') }}"
                                                                    class="btn btn-primary d-none d-md-inline-flex">
                                                                    <em class="icon ni ni-plus"></em>
                                                                    <span>Add</span>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal for adding students -->
                            <div class="modal fade" id="makeSchoolAdminModal" tabindex="-1" aria-labelledby="makeSchoolAdminModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header text-white" style="background-color: #D17E00;">
                                            <h5 class="modal-title" id="makeSchoolAdminModalLabel">Make School Admin</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <form action="{{ route('users.makeSchoolAdmin') }}" method="GET" enctype="multipart/form-data">
                                                <input type="hidden" name="user_id" id="user_id" value="">
                                                <div class="mb-3">
                                                    @role('Admin')
                                                    @php
                                                    $schools = App\Models\School::all();
                                                    @endphp
                                                    <label for="school_id" class="form-label">Select School:</label>
                                                    <select name="school_id" id="school_id" class="form-select" required>
                                                        <option value="" disabled selected>Select a school</option>
                                                        @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}"
                                                            {{ request('school') == $school->id ? 'selected' : '' }}>
                                                            {{ $school->name }}
                                                        </option>
                                                        @endforeach
                                                    </select>
                                                    @endrole
                                                </div>
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-warning me-2" class="" style="background-color:#d33; border-color:#d33" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Make School Admin</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nk-block">
                                <form id="mass-delete-form" action="{{ route('users.massDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <table class="table text-center">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="col-1" style="padding-left:8px"><input type="checkbox" id="select-all"></th>
                                                <th class="col-3" style="text-align: left;padding-left:15px;">Name</th>
                                                <th class="col-3" style="text-align: left;padding-left:15px;">Email</th>
                                                <th class="col-3">Role</th>
                                                <th class="col-2" style="padding-right:8px;">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $user)
                                            <tr>
                                                <td class="align-middle" style="padding-left:8px;"><input type="checkbox" class="user-checkbox checkbox" name="ids[]" value="{{ $user->id }}"></td>
                                                <td class="align-middle" style="text-align: left;padding:15px;">{{ $user->name }}</td>
                                                <td class="align-middle" style="text-align: left;padding-left:15px;">{{ $user->email }}</td>
                                                <td class="align-middle">
                                                    @if ($user->roles->isEmpty())
                                                    <span>-</span>
                                                    @else
                                                    @foreach ($user->roles->pluck('name')->map(function ($role) {
                                                    if ($role === 'Cordinator') {
                                                    return null; // Skip "Cordinator" role
                                                    } elseif ($role === 'Admin') {
                                                    return 'Super Admin';
                                                    } elseif ($role === 'school') {
                                                    return 'School Admin';
                                                    } else {
                                                    return $role;
                                                    }
                                                    })->filter() as $role)
                                                    {{ $role }}<br>
                                                    @endforeach
                                                    @endif
                                                </td>
                                                <td class="align-middle" style="padding-right:8px;">
                                                    <div class="d-flex align-items-center justify-content-center" style="gap:10px;">
                                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit Users</a>
                                                        <!-- @if (!$user->hasRole('Admin'))
                                                        <a href="{{ route('users.makeAdmin', $user->id) }}" class="btn btn-primary" style="justify-content: center; width:55px;" title="Add Super Admin Role"><i class="fa-solid fa-user-pen"></i></a>
                                                        @else
                                                        <a href="{{ route('users.removeAdmin', $user->id) }}" class="btn btn-danger" style="justify-content: center;width:55px;" title="Remove Super Admin Role">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </a>
                                                        @endif
                                                        @if (!$user->hasRole('school'))
                                                        <button type="button" class="btn btn-primary" title="Add School Admin Role" data-bs-toggle="modal" data-bs-target="#makeSchoolAdminModal" data-user-id="{{ $user->id }}" style="justify-content: center; width:55px;">
                                                            <i class="fa-solid fa-school"></i>
                                                        </button>
                                                        @else
                                                        <a href=" {{ route('users.removeSchoolAdmin', $user->id) }}" class="btn btn-danger"
                                                            title="Remove School Admin Role" style="justify-content: center; width:55px;"><i class="fa-solid fa-trash"></i>
                                                        </a>
                                                        @endif -->
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <div class="card-inner">
                                        {!! $users->links('pagination::bootstrap-4') !!}
                                    </div>
                                </form>
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
        $(document).ready(function() {
            // Initialize select2 for the filters
            $('.js-select2').select2();
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var makeSchoolAdminModal = document.getElementById('makeSchoolAdminModal');
        makeSchoolAdminModal.addEventListener('show.bs.modal', function(event) {
            // Button that triggered the modal
            var button = event.relatedTarget;
            // Extract user ID from data-user-id attribute
            var userId = button.getAttribute('data-user-id');
            // Update the hidden input field in the modal
            var userIdInput = makeSchoolAdminModal.querySelector('#user_id');
            userIdInput.value = userId;
        });
    });
</script>
@if(session('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: @json(session('success')),
        icon: 'success',
        confirmButtonText: 'Ok'
    });
</script>
@endif

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
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    function massDelete() {
        const selectedUsers = document.querySelectorAll('.user-checkbox:checked');
        if (selectedUsers.length === 0) {
            Swal.fire({
                title: 'Delete Users',
                text: "Please select at least one user to delete.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Are you sure you want to delete\n' + selectedUsers.length + ' Users?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('mass-delete-form').submit();
            }
        })
    }
</script>
@endsection