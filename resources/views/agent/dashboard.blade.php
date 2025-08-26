@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Welcome, {{ $user->name }}</li>
    </ol>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Dashboard</h1>
        @if($incompleteCustomersCount > 0)
            <div class="btn btn-secondary" 
                 style="cursor: not-allowed; opacity: 0.6;" 
                 title="Complete customer information first">
                <i class="fas fa-money-bill-wave me-1"></i>
                Enter Collection (Restricted)
            </div>
        @else
            <a href="{{ route('collections.index') }}" class="btn btn-primary">
                <i class="fas fa-money-bill-wave me-1"></i>
                Enter Collection
            </a>
        @endif
    </div>

    @include('components.account-expiration-popup')
    
    <!-- Incomplete Customer Warning -->
    @php
        $incompleteCustomersCount = \App\Models\Customer::where('agent_id', Auth::user()->id)->incomplete()->count();
    @endphp
    
    @if($incompleteCustomersCount > 0)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3 text-warning"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">
                        <i class="fas fa-user-times"></i> Incomplete Customer Information
                    </h5>
                    <p class="mb-2">
                        You have <strong>{{ $incompleteCustomersCount }}</strong> customers with incomplete information. 
                        Please complete their details before performing lot imports or collection operations.
                    </p>
                    <div>
                        <a href="{{ route('agent.customers.index') }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-users"></i> Complete Customer Info
                        </a>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Lot and collection imports are restricted until all customer information is complete.
                        </small>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Customers</h5>
                    <h2 class="card-text">{{ $totalCustomers }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">RD Accounts</h5>
                    <h2 class="card-text">{{ $totalAccounts }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Total Payments</h5>
                    <h2 class="card-text">{{ $totalPayments }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Today's Collections</h5>
                    <h2 class="card-text">{{ $todayPayments }}</h2>
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
                            @if($incompleteCustomersCount > 0)
                                <div class="btn btn-secondary btn-lg w-100 d-flex align-items-center justify-content-center" 
                                     style="cursor: not-allowed; opacity: 0.6;" 
                                     title="Complete customer information first">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    <div>
                                        <div class="fw-bold">Sync Data</div>
                                        <small class="text-muted">Restricted</small>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('agent.excel-import.index') }}" class="btn btn-success btn-lg w-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    <div>
                                        <div class="fw-bold">Sync Data</div>
                                        <small>Import Excel Files</small>
                                    </div>
                                </a>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('agent.rd-agent-accounts.create') }}" class="btn btn-info btn-lg w-100 d-flex align-items-center justify-content-center">
                                <i class="fas fa-plus-circle me-2"></i>
                                <div>
                                    <div class="fw-bold">New RD Account</div>
                                    <small>Manual Entry</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            @if($incompleteCustomersCount > 0)
                                <div class="btn btn-secondary btn-lg w-100 d-flex align-items-center justify-content-center" 
                                     style="cursor: not-allowed; opacity: 0.6;" 
                                     title="Complete customer information first">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <div>
                                        <div class="fw-bold">Enter Collection</div>
                                        <small class="text-muted">Restricted</small>
                                    </div>
                                </div>
                            @else
                                <a href="{{ route('collections.create') }}" class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    <div>
                                        <div class="fw-bold">Enter Collection</div>
                                        <small>Record Payment</small>
                                    </div>
                                </a>
                            @endif
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('agent.rd-agent-accounts.export') }}" class="btn btn-warning btn-lg w-100 d-flex align-items-center justify-content-center">
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

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">RD Account Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Active Accounts</h5>
                                    <h2 class="card-text">{{ $rdStats['active'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Completed Accounts</h5>
                                    <h2 class="card-text">{{ $rdStats['completed'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Pending Accounts</h5>
                                    <h2 class="card-text">{{ $rdStats['pending'] }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Defaulted Accounts</h5>
                                    <h2 class="card-text">{{ $rdStats['defaulted'] }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Customers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Date Added</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCustomers as $customer)
                                <tr>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->phone }}</td>
                                    <td>{{ $customer->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Recent Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                <tr>
                                    <td>{{ $payment->customer->name }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->created_at->format('d/m/Y') }}</td>
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
    }
    .card-footer {
        background-color: rgba(0, 0, 0, 0.1);
        border-top: none;
    }
</style>
@endpush 