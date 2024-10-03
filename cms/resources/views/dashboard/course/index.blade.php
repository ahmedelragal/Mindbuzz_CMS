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
                                        <h3 class="nk-block-title page-title">Courses</h3>
                                    </div>
                                    <div class="nk-block-head-content">
                                        <div class="toggle-wrap nk-block-tools-toggle"><a href="#"
                                                class="btn btn-icon btn-trigger toggle-expand me-n1"
                                                data-target="more-options"><em class="icon ni ni-more-v"></em></a>
                                            <div class="toggle-expand-content" data-content="more-options">
                                                <ul class="nk-block-tools g-3">

                                                    <li class="nk-block-tools-opt"><a
                                                            class="btn btn-icon btn-warning d-md-none"
                                                            data-bs-toggle="modal" href="#student-add"><em
                                                                class="icon ni ni-plus"></em></a>
                                                        <a href="{{ route('courses.create') }}"
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
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Course</th>
                                        <th scope="col" class="text-center">Action</th>

                                    </tr>
                                </thead>
                                <tbody class="">
                                    @foreach ($courses as $course)
                                    <tr>
                                        <th scope="row">{{ $course->id }}</th>
                                        <td>{{ $course->name }}</td>
                                        <td class="d-flex flex-row justify-content-center">
                                            <a href="{{ route('courses.edit', $course->id) }}"
                                                class="btn btn-warning me-1">Edit</a>



                                            <form id="delete-form-{{ $course->id }}" action="{{ route('courses.destroy', $course->id) }}"
                                                method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')


                                                <div class="d-lg-flex d-none">

                                                </div>

                                            </form>
                                            <button type="submit" class="btn btn-danger"
                                                onclick="confirmDelete({{ $course->id }})">Delete</button>
                                        </td>
                                    </tr>
                                    @endforeach

                                </tbody>
                            </table>
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
</div>
@include('dashboard.layouts.footer')

</div>
</div>
</div>
@endsection
@section('page_js')
<script>
    function confirmDelete(courseId) {
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
                document.getElementById('delete-form-' + courseId).submit();
            }
        })
    }
</script>
@endsection