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
                                <div class="nk-block-head nk-block-head-sm">
                                    <div class="nk-block-between">
                                        <div class="nk-block-head-content">
                                            <h3 class="nk-block-title page-title">Cluster</h3>
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
                                                            <!--@can('program-create')-->
                                                            <!--    <a href="{{ route('programs.create') }}"-->
                                                            <!--        class="btn btn-primary d-none d-md-inline-flex">-->
                                                            <!--        <em class="icon ni ni-plus"></em>-->
                                                            <!--        <span>Add</span>-->
                                                            <!--    @endcan-->
                                                            <!--</a>-->
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
                                            <th scope="col">Name</th>
                                            <!--<th scope="col">School</th>-->
                                            <!--<th scope="col">Course</th>-->
                                            <th scope="col" class="text-center">Action</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($programs as $programName => $groupedPrograms)
                                            
                                            <tr>
                                                <th scope="row">{{ $groupedPrograms->id }}</th>
                                                <td>{{ $groupedPrograms->name }}</td>
                                                <!--<td>{{ $groupedPrograms->first()->school ? $groupedPrograms->first()->school->name : '-' }}-->
                                                </td>
                            <!--                    <td>-->

                            <!--                        @foreach ($groupedPrograms->take(1) as $program)-->
                            <!--                            @if (isset($program->course) && isset($program->stage))-->
                            <!--                                {{ $program->course->name }}/{{ $program->stage->name }}<br>-->

                            <!--                            @endif-->
                            <!--                        @endforeach-->

                            <!--                        @if ($groupedPrograms->count() > 1)-->
                            <!--                            <button-->
                            <!--                                class="btn btn-gray view-more d-flex flex-row justify-content-end "-->
                            <!--                                data-program-name="{{ $programName }}">View More</button>-->
                            <!--                        @endif-->
                            <!--                        <div class="more-courses d-none"-->
                            <!--                            @if (isset($groupedPrograms)) data-program-name="{{ $programName }}">-->
                            <!--                                @foreach ($groupedPrograms->skip(1) as $program)-->
                            <!--                                    @if (isset($program->course) && isset($program->stage))-->
                            <!--                                        {{ $program->course->name }}/{{ $program->stage->name }}<br> @endif-->
                            <!--                            @endforeach-->
                            <!--                        @else-->
                            <!--                            <span>-</span>-->
                            <!--            @endif-->
                            <!--</div>-->
                            <!--</td>-->
                            <td class="">
                                <a
                                    href="{{ route('add-curriculum', $groupedPrograms->id) }}"class="btn btn-warning me-1">Add
                                    curriculum</a>
                                <a
                                    href="{{ route('view-curriculum', $groupedPrograms->id) }}"class="btn btn-warning me-1">View
                                    Curriculum</a>

                                {{-- <a href="{{ route('programs.edit', $groupedPrograms->id) }}"
                                                                            class="btn btn-warning me-1">Edit</a> --}}
                                <!--@can('program-delete')-->
                                <!--    <form id="delete-form-{{ $groupedPrograms->id }}"-->
                                <!--        action="{{ route('programs.destroy', $groupedPrograms->id) }}" method="POST"-->
                                <!--        style="display:inline-block;">-->
                                <!--        @csrf-->
                                <!--        @method('DELETE')-->

                                <!--    </form>-->
                                <!--    <button type="submit" class="btn btn-danger"-->
                                <!--        onclick="confirmDelete({{ $groupedPrograms->id }})">Delete</button>-->
                                <!--@endcan-->
                            </td>
                            </tr>
                            @endforeach

                            </tbody>
                            </table>
                            {{-- <div class="mx-auto d-flex justify-content-center">
                                    <div class="nk-block-between-md g-3">
                                        {!! $programs->links() !!}
                                    </div>
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
        document.querySelectorAll('.view-more').forEach(button => {
            button.addEventListener('click', function() {
                const programName = this.getAttribute('data-program-name');
                const moreCoursesDiv = document.querySelector(
                    `.more-courses[data-program-name="${programName}"]`);
                if (moreCoursesDiv.classList.contains('d-none')) {
                    moreCoursesDiv.classList.remove('d-none');
                    this.textContent = 'View Less';
                } else {
                    moreCoursesDiv.classList.add('d-none');
                    this.textContent = 'View More';
                }
            });
        });
    </script>
    <script>
        function confirmDelete(programId) {
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
                    document.getElementById('delete-form-' + programId).submit();
                }
            })
        }
    </script>
@endsection
