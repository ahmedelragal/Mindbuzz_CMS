@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap">
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
                            <div class="nk-block-head nk-block-head-sm">
                                <div class="nk-block-between">
                                    <div class="nk-block-head-content">
                                        <h3 class="nk-block-title page-title">Classes</h3>
                                    </div>

                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle"><a href="#"
                                                class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                            <div class="toggle-expand-content" data-content="more-options">
                                                <form method="GET" action="{{ route('classes.index') }}">
                                                    @csrf
                                                    <ul class="nk-block-tools g-3">
                                                        @role('Admin')
                                                        <li>
                                                            <div class="drodown">
                                                                <select name="school" class="form-select" id="school_id"
                                                                    onchange="this.form.submit()">
                                                                    <option value="">Select School</option>
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
                                                        <li class="nk-block-tools-opt"><a
                                                                class="btn btn-icon btn-warning d-md-none"
                                                                data-bs-toggle="modal" href="#student-add"><em
                                                                    class="icon ni ni-plus"></em></a>
                                                            @can('class-create')
                                                            <a href="{{ route('classes.create') }}"
                                                                class="btn btn-primary d-none d-md-inline-flex">
                                                                <em class="icon ni ni-plus"></em>
                                                                <span>Add</span>
                                                            </a>
                                                            @endcan
                                                        </li>

                                                    </ul>
                                                </form>


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table">
                                <thead class="thead-dark">
                                    <tr>
                                        <!-- <th scope="col">#</th> -->
                                        <th scope="col">Name</th>
                                        <th scope="col">School</th>
                                        <!-- <th scope="col">About Class</th> -->
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $rowNumber = ($pagedClasses->currentPage() - 1) * $pagedClasses->perPage() + 1; @endphp
                                    @foreach ($pagedClasses as $class)
                                    <tr>
                                        <!-- <th scope="row">{{ $rowNumber++ }}</th> -->
                                        <td>{{ $class->name }}</td>
                                        <td>{{ $class->school->name ?? 'No School' }}</td>
                                        <!-- <td> -->
                                        <!-- <button class="btn btn-primary view-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePrograms{{ $loop->iteration }}" aria-expanded="false" aria-controls="collapsePrograms{{ $loop->iteration }}"> -->
                                        <!-- View Programs -->
                                        <!-- </button> -->
                                        <!-- <div class="collapse mt-2" id="collapsePrograms{{ $loop->iteration }}">
                                                <ul>
                                                    <li>{{ optional($class->program)->name ?? '-' }} -
                                                        {{ optional($class->program)->course->name ?? 'No Course' }} -
                                                        {{ optional($class->stage)->name ?? 'No Stage' }}
                                                    </li>
                                                </ul>
                                            </div> -->
                                        <!-- </td> -->
                                        <td class="d-flex flex-row justify-content-left">
                                            <a href="{{ route('classes.view', $class->id) }}" class="btn btn-warning me-1">View</a>
                                            @can('class-delete')
                                            <form id="delete-form-{{ $class->id }}" action="{{ route('classes.destroy', $class->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="submit" class="btn btn-danger" onclick="confirmDelete({{ $class->id }})">Delete</button>
                                            @endcan
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="card-inner">
                                {!! $pagedClasses->appends(request()->except('page'))->links('pagination::bootstrap-4') !!}
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
@endsection