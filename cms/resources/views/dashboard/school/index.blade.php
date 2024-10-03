@extends('dashboard.layouts.layout')
@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap ">
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
                                        <h3 class="nk-block-title page-title">Schools</h3>
                                    </div>
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle"><a href="#"
                                                class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                            <div class="toggle-expand-content" data-content="more-options">
                                                <ul class="nk-block-tools g-3">

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
                            <table class="table">
                                <thead class="thead-dark">
                                    @if ($schools->count() > 0)
                                    <tr>
                                        <!-- <th scope="col">#</th> -->
                                        <th scope="col">Name</th>
                                        <th scope="col">Email</th>
                                        <!--<th scope="col">Phone</th>-->
                                        <th scope="col">Students</th>
                                        <th scope="col">Instructors</th>
                                        <th scope="col" class="text-center">Action</th>

                                    </tr>
                                    @endif
                                </thead>
                                <tbody>
                                    @foreach ($schools as $school)
                                    <tr>
                                        <!-- <th scope="row">{{ $school->id }}</th> -->
                                        <td>{{ $school->name }}</td>
                                        <td>{{ $school->email }}</td>
                                        <!--<td>{{ $school->phone }}</td>-->
                                        <td>{{ \App\Models\User::where('school_id', $school->id)->where('role', 2)->count() }}
                                        <td>{{ \App\Models\User::where('school_id', $school->id)->where('role', 1)->count() }}
                                        </td>
                                        <!--<td>-->
                                        <!--    School-->
                                        <!--</td>-->
                                        <td class="d-flex flex-row justify-content-center">
                                            @can('school-edit')
                                            <a href="{{ route('schools.edit', $school->id) }}"
                                                class="btn btn-warning me-1">Edit</a>
                                            @endcan


                                            @can('school-delete')
                                            <form id="delete-form-{{ $school->id }}"
                                                action="{{ route('schools.destroy', $school->id) }}" method="POST"
                                                style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')


                                                <div class="d-lg-flex d-none">

                                                </div>

                                            </form>
                                            <button type="submit" class="btn btn-danger"
                                                onclick="confirmDelete({{ $school->id }})">Delete</button>
                                            @endcan
                                            <!--<td class="">-->

                                            <a style="margin-left:2px"
                                                href="{{ route('add-curriculum', $school->id) }}" class="btn btn-warning me-1">Add
                                                curriculum</a>
                                            <a style="margin-left:-3px"
                                                href="{{ route('view-curriculum', $school->id) }}" class="btn btn-warning me-1">View
                                                Curriculum</a>
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
                            <div class="card-inner">
                                {!! $schools->links('pagination::bootstrap-4') !!}
                            </div>
                            {{-- <div class="nk-block">
                                    <div class="card">

                                        <table class="card-inner-group">
                                            <div class="card-inner p-0">
                                                <div class="nk-tb-list nk-tb-ulist">
                                                    <thead class="nk-tb-item nk-tb-head">
                                                        <tr>
                                                            <th ><span class="sub-text">#</span>
                                                            </th>
                                                            <th ><span class="sub-text">Course</span>
                                                            </th>
                                                        </tr>



                                                    </thead>
                                                <tbody class="nk-tb-item">
                                                    @foreach ($courses as $course)
                                                        <tr class="nk-tb-col d-flex justify-content-between">
                                                            <td class="user-card justify-content-end">

                                                                <div class="user-info">
                                                                    <span class="tb-lead">{{ $course->id }} <span
                                class="dot dot-warning d-md-none ms-1"></span></span>
                        </div>
                        </td>
                        <td class="user-card justify-content-end">

                            <div class="user-info">
                                <span class="tb-lead">{{ $course->name }} <span
                                        class="dot dot-warning d-md-none ms-1"></span></span>
                            </div>
                        </td>
                        <td class="d-flex justify-content-end">
                            <a href="{{ route('courses.edit', $course->id) }}"
                                class="btn btn-warning me-1">Edit</a>



                            <form action="{{ route('courses.destroy', $course->id) }}"
                                method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this course?')">Delete</button>

                                <div class="d-lg-flex d-none">

                                </div>

                            </form>


                        </td>

                        </tr>


                        <hr>
                        @endforeach
                        </tbody>



                    </div>
                    <div class="d-flex justify-content-center">
                        {!! $courses->links() !!}
                    </div>
                </div>
                </table>

            </div> --}}
        </div>
    </div>
</div>
</div>
@include('dashboard.layouts.footer')
</div>

</div>
</div>
</div>
@endsection
@section('page_js')
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
@endsection