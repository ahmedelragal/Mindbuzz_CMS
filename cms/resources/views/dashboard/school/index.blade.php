@php
session(['schools_previous_url' => url()->full()]);
@endphp
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
                            <div class="nk-block-head nk-block-head-sm">
                                <div>
                                    <div class="nk-block-head-content" style="display:flex; align-items:center; justify-content:space-between">
                                        <h3 class="nk-block-title page-title">School List</h3>
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
                                                                <a href="{{ route('schools.create') }}"
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
                            <div class="nk-block">
                                <form id="mass-delete-form" action="{{ route('schools.massDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <table class="table text-center">
                                        @if ($schools->count() > 0)
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="col-1" style="padding-left:8px"><input type="checkbox" id="select-all"></th>
                                                <th class="col-3" style="text-align: left;padding-left:15px;">Name</th>
                                                <th class="col-3" style="text-align: left;padding-left:15px;">Email</th>
                                                <th class="col-2">Students</th>
                                                <th class="col-2">Instructors</th>
                                                <th class="col-1" style="padding-right:8px;">Action</th>
                                            </tr>
                                            @endif
                                        </thead>
                                        <tbody>
                                            @foreach ($schools as $school)
                                            <tr>
                                                <td class="align-middle" style="padding-left:8px;"><input type="checkbox" class="school-checkbox" name="ids[]" value="{{ $school->id }}"></td>
                                                <td class style="text-align: left;padding: 15px;">{{ $school->name }}</td>
                                                <td class="align-middle" style="text-align: left;padding-left:15px;">{{ $school->email }}</td>
                                                <!--<td>{{ $school->phone }}</td>-->
                                                <td class="align-middle">{{ \App\Models\User::where('school_id', $school->id)->where('role', 1)->count() }}</td>
                                                <td class="align-middle">{{ \App\Models\User::where('school_id', $school->id)->where('role', 2)->count() }}</td>
                                                <td class="align-middle" style="padding-right:8px;">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <a href="{{ route('schools.edit', $school->id) }}" class="btn btn-primary" title="Edit School"><i class="fa-regular fa-pen-to-square"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>
                                <div class="card-inner">
                                    {!! $schools->links('pagination::bootstrap-4') !!}
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
        $(document).ready(function() {
            // Initialize select2 for the filters
            $('.js-select2').select2();
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
<script>
    function confirmDelete(schoolId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + schoolId).submit();
            }
        })
    }
</script>
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.school-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    function massDelete() {
        const selectedSchools = document.querySelectorAll('.school-checkbox:checked');
        if (selectedSchools.length === 0) {
            Swal.fire({
                title: 'Delete Schools',
                text: "Please select at least one school to delete.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Are you sure you want to delete\n' + selectedSchools.length + ' schools?',
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