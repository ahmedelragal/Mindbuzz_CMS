@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main">
        @include('dashboard.layouts.sidebar')
        <div class="nk-wrap ">
            @include('dashboard.layouts.navbar')

            <div class="nk-content ">
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-lg-12 margin-tb mb-4">
                            <div class="pull-left">
                                <h2>Role Management
                                    <div class="float-end">
                                        @can('role-create')
                                        <a class="btn btn-primary" href="{{ route('roles.create') }}"> Create New
                                            Role</a>
                                        @endcan
                                    </div>
                                </h2>
                            </div>
                        </div>
                    </div>

                    @if ($message = Session::get('success'))
                    <div class="alert alert-success">
                        <p>{{ $message }}</p>
                    </div>
                    @endif

                    <table class="table wrapper ">
                        <thead class="thead-dark ">
                            <tr>
                                <th>Name</th>
                                <th class="text-center" width="50%">Action</th>
                            </tr>
                            @foreach ($roles as $key => $role)
                            <tr>
                                <td>{{ $role->name }}</td>
                                <td class="d-flex flex-row justify-content-center">
                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                                        <a class="btn btn-gray" href="{{ route('roles.show', $role->id) }}">Show</a>
                                        @can('role-edit')
                                        <a class="btn btn-warning" href="{{ route('roles.edit', $role->id) }}">Edit</a>
                                        @endcan


                                        @csrf
                                        @method('DELETE')
                                        @can('role-delete')
                                        <button type="submit" class="btn btn-danger">Delete</button>
                                        @endcan
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                    </table>

                    {!! $roles->render() !!}

                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>
@endsection