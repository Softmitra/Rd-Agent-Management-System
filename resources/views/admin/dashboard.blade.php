@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid px-4">
    <h5 class="mt-4">Admin Dashboard</h5>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Welcome, {{ $user->name }}</li>
    </ol>

    <div class="row g-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-user-tie fa-2x"></i>
                        </div>
                        <div>
                            <div class="small">Total Agents</div>
                            <div class="fs-4">{{ \App\Models\Agent::count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('agents.index') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-user-clock fa-2x"></i>
                        </div>
                        <div>
                            <div class="small">Pending Verifications</div>
                            <div class="fs-4">{{ \App\Models\Agent::where('is_verified', false)->count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('agents.index') }}">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div>
                            <div class="small">Total Customers</div>
                            <div class="fs-4">{{ \App\Models\Customer::count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-piggy-bank fa-2x"></i>
                        </div>
                        <div>
                            <div class="small">Total RD Accounts</div>
                            <div class="fs-4">{{ \App\Models\RDAccount::count() }}</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">View Details</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="{{ route('admin.excel-import.index') }}" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-sync-alt me-2"></i>
                                <div>
                                    <div class="fw-bold">Sync Data</div>
                                    <small>Import Excel Files</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('agents.create') }}" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-user-plus me-2"></i>
                                <div>
                                    <div class="fw-bold">Add Agent</div>
                                    <small>Create New Agent</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.rd-accounts.create') }}" class="btn btn-info btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-plus-circle me-2"></i>
                                <div>
                                    <div class="fw-bold">New RD Account</div>
                                    <small>Manual Entry</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.rd-accounts.export') }}" class="btn btn-warning btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-download me-2"></i>
                                <div>
                                    <div class="fw-bold">Export Data</div>
                                    <small>Download Reports</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-tie me-1"></i>
                    Recent Agents
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Customers</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(\App\Models\Agent::latest()->take(5)->get() as $agent)
                                <tr>
                                    <td>{{ $agent->name }}</td>
                                    <td>
                                        @if($agent->is_verified)
                                            <span class="badge bg-success">Verified</span>
                                        @else
                                            <span class="badge bg-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $agent->customers()->count() }}</td>
                                    <td>
                                        <a href="{{ route('agents.show', $agent) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Monthly Statistics
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>New Agents</th>
                                    <th>New Customers</th>
                                    <th>New RD Accounts</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $months = collect([]);
                                    for ($i = 0; $i < 5; $i++) {
                                        $date = now()->subMonths($i);
                                        $months->push([
                                            'month' => $date->format('M Y'),
                                            'agents' => \App\Models\Agent::whereMonth('created_at', $date->month)
                                                ->whereYear('created_at', $date->year)->count(),
                                            'customers' => \App\Models\Customer::whereMonth('created_at', $date->month)
                                                ->whereYear('created_at', $date->year)->count(),
                                            'accounts' => \App\Models\RDAccount::whereMonth('created_at', $date->month)
                                                ->whereYear('created_at', $date->year)->count(),
                                        ]);
                                    }
                                @endphp
                                @foreach($months as $month)
                                <tr>
                                    <td>{{ $month['month'] }}</td>
                                    <td>{{ $month['agents'] }}</td>
                                    <td>{{ $month['customers'] }}</td>
                                    <td>{{ $month['accounts'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgb(33 40 50 / 15%);
        transition: transform 0.2s ease-in-out;
    }
    .card:hover {
        transform: translateY(-3px);
    }
    .card-footer {
        background-color: rgba(0, 0, 0, 0.1);
        border-top: none;
        padding: 0.75rem 1.25rem;
    }
    .card-body {
        padding: 1.25rem;
    }
    .card .fa-2x {
        opacity: 0.85;
    }
    .fs-4 {
        font-size: 1.5rem !important;
        font-weight: 600;
        margin-top: 0.25rem;
    }
</style>
@endpush 