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
                                <h2> Show Role
                                    <div class="float-end">
                                        <a class="btn btn-primary" href="{{ route('roles.index') }}"> Back</a>
                                    </div>
                                </h2>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-xs-12 mb-3">
                            <div class="form-group">
                                <strong>Name:</strong>
                                {{ $role->name }}
                            </div>
                        </div>
                        <div class="col-xs-12 mb-3">
                            <div class="form-group">
                                <strong>Permissions:</strong>
                                @if (!empty($rolePermissions))
                                @foreach ($rolePermissions as $v)
                                <label class="label label-secondary text-dark">{{ $v->name }},</label>
                                @endforeach
                                @endif
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