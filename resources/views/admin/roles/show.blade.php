@extends('layouts.app')

@section('title', 'View Role')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>View Role</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('roles.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                @if($role->name !== 'admin')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Role
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Role Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Name</label>
                        <p class="form-control-static">{{ $role->name }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Display Name</label>
                        <p class="form-control-static">{{ $role->display_name }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Description</label>
                        <p class="form-control-static">{{ $role->description ?? 'No description available.' }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Created At</label>
                        <p class="form-control-static">{{ $role->created_at->format('F j, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Last Updated</label>
                        <p class="form-control-static">{{ $role->updated_at->format('F j, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 