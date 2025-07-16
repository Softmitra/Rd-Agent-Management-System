@extends('layouts.app')

@section('title', 'Manage Customers')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h5>Manage Customers</h5>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Customer
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Total Customers</div>
                            <div class="font-weight-bold">{{ $summary['total_customers'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">
                        New this month: {{ $summary['recent_customers'] }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">With RD Accounts</div>
                            <div class="font-weight-bold">{{ $summary['with_rd_accounts'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-piggy-bank fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">Total RD Accounts: {{ $summary['total_rd_accounts'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Without RD Accounts</div>
                            <div class="font-weight-bold">{{ $summary['without_rd_accounts'] }}</div>
                        </div>
                        <div>
                            <i class="fas fa-user-clock fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">Potential customers for RD</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small text-white-50">Average RD Accounts</div>
                            <div class="font-weight-bold">
                                {{ number_format($summary['with_rd_accounts'] > 0 ? $summary['total_rd_accounts'] / $summary['with_rd_accounts'] : 0, 1) }}
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-chart-line fa-2x opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <div class="small text-white">Per customer with RD</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('customers.index') }}" method="GET" class="form-inline">
                        <div class="form-group mb-2 mr-2">
                            <label for="search" class="mr-2">Search:</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name, Email, Mobile...">
                        </div>
                        <div class="form-group mb-2 mr-2">
                            <label for="agent_id" class="mr-2">Agent:</label>
                            <select class="form-control" id="agent_id" name="agent_id">
                                <option value="">All Agents</option>
                                @foreach($agents as $agent)
                                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                        {{ $agent->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2 mr-2">
                            <label for="date_range" class="mr-2">Registration Date:</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" 
                                   value="{{ request('date_range') }}" placeholder="Select date range">
                        </div>
                        <div class="form-group mb-2 mr-2">
                            <label for="has_rd_account" class="mr-2">RD Account:</label>
                            <select class="form-control" id="has_rd_account" name="has_rd_account">
                                <option value="">All</option>
                                <option value="yes" {{ request('has_rd_account') === 'yes' ? 'selected' : '' }}>With RD Account</option>
                                <option value="no" {{ request('has_rd_account') === 'no' ? 'selected' : '' }}>Without RD Account</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mb-2">Filter</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-default mb-2 ml-2">Reset</a>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customers List</h3>
                    <div class="card-tools">
                        <a href="{{ route('customers.export') }}?{{ http_build_query(request()->query()) }}" 
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Agent</th>
                                <th>Registration Date</th>
                                <th>RD Accounts</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->mobile_number }}</td>
                                    <td>
                                        @if($customer->agent)
                                            <a href="{{ route('agents.show', $customer->agent) }}">
                                                {{ $customer->agent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">No Agent</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if($customer->rdAccounts->count() > 0)
                                            <span class="badge badge-success">{{ $customer->rdAccounts->count() }}</span>
                                        @else
                                            <span class="badge badge-danger">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                    title="Delete" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(function() {
    // Initialize date range picker
    $('#date_range').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear',
            format: 'DD/MM/YYYY'
        }
    });

    $('#date_range').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));
    });

    $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
});
</script>
@endpush 