@extends('layouts.app')

@section('content')
    <div class="container-fluid px-4">

        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">RD Accounts</h5>
                    <div>
                        <a href="{{ route('admin.rd-accounts.create') }}" class="btn btn-primary">Create New Account</a>
                        <a href="{{ route('admin.rd-accounts.export') }}" class="btn btn-secondary">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </a>
                    </div>
                </div>

                <!-- Filters -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                            placeholder="Search account number...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="matured" {{ request('status') == 'matured' ? 'selected' : '' }}>Matured</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min Amount</label>
                        <input type="number" name="min_amount" class="form-control" value="{{ request('min_amount') }}"
                            placeholder="Min amount...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Amount</label>
                        <input type="number" name="max_amount" class="form-control" value="{{ request('max_amount') }}"
                            placeholder="Max amount...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.rd-accounts.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-white">
                                        <h6 class="text-uppercase mb-2">Total Accounts</h6>
                                        <h2 class="mb-0">{{ $summary['total_accounts'] }}</h2>
                                    </div>
                                    <div class="text-white">
                                        <i class="fas fa-users fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-white">
                                        <h6 class="text-uppercase mb-2">Active Accounts</h6>
                                        <h2 class="mb-0">{{ $summary['status_counts']['active'] }}</h2>
                                    </div>
                                    <div class="text-white">
                                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-white">
                                        <h6 class="text-uppercase mb-2">Total Monthly Deposits</h6>
                                        <h2 class="mb-0">₹{{ number_format($summary['total_monthly_deposits'], 2) }}</h2>
                                    </div>
                                    <div class="text-white">
                                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-white">
                                        <h6 class="text-uppercase mb-2">Matured Accounts</h6>
                                        <h2 class="mb-0">{{ $summary['status_counts']['matured'] }}</h2>
                                    </div>
                                    <div class="text-white">
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Account Number</th>
                                <th>Customer Name</th>
                                <th>Agent Name</th>
                                <th>Monthly Amount</th>
                                <th>Total Deposited</th>
                                <th>Opening Date</th>
                                <th>Half Month Period</th>
                                <th>Installments Paid</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rdAccounts as $account)
                                <tr>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->customer->name }}</td>
                                    <td>{{ $account->agent->name ?? 'N/A' }}</td>
                                    <td>₹{{ number_format($account->monthly_amount, 2) }}</td>
                                    <td>₹{{ number_format($account->total_deposited, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($account->start_date)->format('d/m/Y') }}</td>
                                    <td>{{ ucfirst($account->half_month_period) }} Half</td>
                                    <td>{{ $account->installments_paid }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'matured' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($account->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.rd-accounts.show', $account) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.rd-accounts.edit', $account) }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No RD accounts found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $rdAccounts->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card h2 {
            font-size: 1.75rem;
        }

        .btn-group {
            margin-left: 0.5rem;
        }

        .badge {
            padding: 0.5em 0.75em;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
        }

        .opacity-50 {
            opacity: 0.5;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any JavaScript functionality here
        });
    </script>
@endpush
