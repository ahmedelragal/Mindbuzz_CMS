@extends('dashboard.layouts.layout')

@section('content')
<div class="nk-app-root">
    <div class="nk-main ">
        @include('dashboard.layouts.sidebar')

        <div class="nk-wrap ">
            @include('dashboard.layouts.navbar')

            <div class="nk-content ">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12 margin-tb mb-4">
                            <div class="pull-left">
                                <h2>Create New Role
                                    <div class="float-end">
                                        <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
                                    </div>
                                </h2>
                            </div>
                        </div>
                    </div>

                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>Name:</strong>
                                    <input type="text" name="name" class="form-control" placeholder="Name">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>Permission:</strong>
                                    <br />
                                    @foreach ($permission as $value)
                                    <label>
                                        <input type="checkbox" name="permission[]" value="{{ $value->id }}"
                                            class="name">
                                        {{ $value->name }}</label>
                                    <br />
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            @include('dashboard.layouts.footer')
        </div>
    </div>
</div>

@endsection