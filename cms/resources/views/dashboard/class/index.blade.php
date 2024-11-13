@php
session(['classes_previous_url' => url()->full()]);
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
                                        <h3 class="nk-block-title page-title">Class List</h3>
                                        <div class="nk-block-head nk-block-head-sm" style="margin-top: 20px;">
                                            <div class="nk-block-head-content" style="display:flex; gap:10px;justify-content:space-between">
                                                <div class="toggle-wrap nk-block-tools-toggle">
                                                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                        data-target="more-options">
                                                        <em class="icon ni ni-more-v"></em>
                                                    </a>
                                                    <div class="toggle-expand-content " data-content="more-options">
                                                        <form method="GET" action="{{ route('classes.index') }}">
                                                            <ul class="nk-block-tools d-flex justify-content-between" style="display:flex; gap:10px;">
                                                                @role('Admin')
                                                                <li>
                                                                    <div class="drodown" style="width:190px;">
                                                                        <select name="school" class="form-select js-select2" id="school_id" style="width:170px;"
                                                                            onchange="this.form.submit()">
                                                                            <option value="">All Schools</option>
                                                                            @foreach ($schools as $school)
                                                                            <option value="{{ $school->id }}"
                                                                                {{ request('school') == $school->id ? 'selected' : '' }}>
                                                                                {{ $school->name }}
                                                                            </option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </li>
                                                                @endrole
                                                                <li>
                                                                    <button type="button" onclick="massDelete()" class="btn btn-danger">Delete Selected</button>
                                                                </li>
                                                                <li class="nk-block-tools-opt"><a
                                                                        class="btn btn-icon btn-primary d-md-none"
                                                                        data-bs-toggle="modal" href="#student-add"><em
                                                                            class="icon ni ni-plus"></em></a>
                                                                    <a href="{{ route('classes.create') }}"
                                                                        class="btn btn-primary d-none d-md-inline-flex">
                                                                        <em class="icon ni ni-plus"></em>
                                                                        <span>Add</span>
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="nk-block">
                                <form id="mass-delete-form" action="{{ route('classes.massDestroy') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <table class="table text-center">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th class="col-1" style="padding-left:8px;"><input type="checkbox" id="select-all"></th>
                                                <th class="col-3" style="text-align: left;padding-left:15px;">Name</th>
                                                <th class="col-2" style="text-align: left;padding-left:15px;">School</th>
                                                <th class="col-2">Students</th>
                                                <th class="col-2">Teachers</th>
                                                <th class="col-2" style="padding-right:8px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($pagedClasses as $class)
                                            <tr>
                                                <td class="align-middle" style="padding-left:8px;"><input type="checkbox" class="class-checkbox" name="ids[]" value="{{ $class->id }}"></td>
                                                <td class style="text-align: left;padding: 15px;">{{ $class->name }}</td>
                                                <td style="text-align: left;padding: 15px;">{{ $class->school->name ?? 'No School' }}</td>

                                                <td class="align-middle">{{ \App\Models\GroupStudent::where('group_id', $class->id)->distinct('student_id')->count('student_id')  }}</td>
                                                <td class="align-middle">{{ \App\Models\GroupTeachers::where('group_id', $class->id)->distinct('teacher_id')->count('teacher_id') }}</td>
                                                <td class="align-middle" style="padding-right:8px;">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="col-4 ">
                                                            <a href="{{ route('classes.view', $class->id) }}" class="btn btn-primary" title="View Class"><i class="fa-solid fa-eye"></i></a>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </form>
                                <div class="card-inner">
                                    {!! $pagedClasses->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
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
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Close any other opened collapses
            document.querySelectorAll('.collapse').forEach(collapse => {
                if (collapse !== document.querySelector(button.dataset.bsTarget)) {
                    collapse.classList.remove('show');
                }
            });
        });
    });


    function confirmDelete(classId) {
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
                document.getElementById('delete-form-' + classId).submit();
            }
        })
    }
</script>
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.class-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    function massDelete() {
        const selectedClasses = document.querySelectorAll('.class-checkbox:checked');
        if (selectedClasses.length === 0) {
            Swal.fire({
                title: 'Delete Classes',
                text: "Please select at least one class to delete.",
                icon: 'warning',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }
        Swal.fire({
            title: 'Are you sure you want to delete\n ' + selectedClasses.length + ' classes?',
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