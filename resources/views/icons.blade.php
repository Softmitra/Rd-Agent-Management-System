@extends('layouts.app')

@section('content')
<div class="container py-5">
    <h1 class="mb-4">Icon Packs Demo</h1>
    
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Font Awesome Icons</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="fas fa-user fa-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-success">
                            <i class="fas fa-check fa-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-info">
                            <i class="fas fa-info fa-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-warning">
                            <i class="fas fa-exclamation-triangle fa-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-danger">
                            <i class="fas fa-times fa-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Material Icons</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="icon icon-bg icon-bg-primary">
                            <span class="material-icons">person</span>
                        </div>
                        <div class="icon icon-bg icon-bg-success">
                            <span class="material-icons">check</span>
                        </div>
                        <div class="icon icon-bg icon-bg-info">
                            <span class="material-icons">info</span>
                        </div>
                        <div class="icon icon-bg icon-bg-warning">
                            <span class="material-icons">warning</span>
                        </div>
                        <div class="icon icon-bg icon-bg-danger">
                            <span class="material-icons">close</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Bootstrap Icons</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="bi bi-person bi-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-success">
                            <i class="bi bi-check bi-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-info">
                            <i class="bi bi-info bi-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-warning">
                            <i class="bi bi-exclamation-triangle bi-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-danger">
                            <i class="bi bi-x bi-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Feather Icons</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-3">
                        <div class="icon icon-bg icon-bg-primary">
                            <i data-feather="user" class="feather-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-success">
                            <i data-feather="check" class="feather-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-info">
                            <i data-feather="info" class="feather-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-warning">
                            <i data-feather="alert-triangle" class="feather-icon"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-danger">
                            <i data-feather="x" class="feather-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Icon Sizes</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-4">
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="fas fa-user icon-sm"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="fas fa-user icon-lg"></i>
                        </div>
                        <div class="icon icon-bg icon-bg-primary">
                            <i class="fas fa-user icon-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 