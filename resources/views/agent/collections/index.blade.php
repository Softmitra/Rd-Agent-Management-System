@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Collection Entries</h5>
        <div>
            <a href="{{ route('collections.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Collection Entry
            </a>
            <a href="{{ route('admin.collectionslist.export', request()->query()) }}" class="btn btn-secondary">
                <i class="fas fa-file-excel"></i> Export
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <!-- Filters -->
            <form action="{{ route('collections.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" 
                               class="form-control" 
                               id="customer_name" 
                               name="customer_name" 
                               placeholder="Search by customer name"
                               value="{{ request('customer_name') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" 
                               class="form-control" 
                               id="date" 
                               name="date" 
                               value="{{ request('date') }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Status</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Submitted</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="btn-group w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('collections.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="text-muted">
                Showing {{ $collections->firstItem() ?? 0 }} to {{ $collections->lastItem() ?? 0 }} 
                of {{ $collections->total() }} results
            </span>
        </div>
        <div>
            @if(request()->hasAny(['customer_name', 'date', 'status']))
                <small class="text-muted">
                    <i class="fas fa-filter"></i> Filters applied
                </small>
            @endif
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Customer Name</th>
                            <th>Payment Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Note</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($collections as $collection)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($collection->date)->format('d/m/Y') }}</td>
                            <td>
                                @if($collection->customer)
                                    <strong>{{ $collection->customer->name }}</strong>
                                    @if($collection->customer->mobile_number)
                                        <br><small class="text-muted">{{ $collection->customer->mobile_number }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $collection->payment_type == 'cash' ? 'success' : 'primary' }}">
                                    {{ strtoupper($collection->payment_type) }}
                                </span>
                            </td>
                            <td>
                                <strong>₹{{ number_format($collection->amount, 2) }}</strong>
                            </td>
                            <td>
                                @php
                                    $statusColors = [
                                        'submitted' => 'warning',
                                        'verified' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger'
                                    ];
                                    $color = $statusColors[$collection->status] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }}">
                                    {{ ucfirst($collection->status) }}
                                </span>
                            </td>
                            <td>
                                @if($collection->note)
                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                          title="{{ $collection->note }}">
                                        {{ $collection->note }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $collection->created_at->format('d/m/Y H:i') }}
                                </small>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-info" 
                                            title="View Details"
                                            onclick="alert('Collection ID: {{ $collection->id }}')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    @if($collection->status == 'submitted')
                                        <button class="btn btn-sm btn-outline-warning" 
                                                title="Edit Collection" 
                                                onclick="alert('Edit functionality coming soon!')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <h5>No Collection Entries Found</h5>
                                    <p>No collections match your current filters.</p>
                                    <a href="{{ route('collections.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Your First Collection Entry
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($collections->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $collections->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    @if($collections->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Collections</h6>
                            <h4>{{ $collections->total() }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Amount</h6>
                            <h4>₹{{ number_format($collections->sum('amount'), 2) }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-rupee-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Cash Collections</h6>
                            <h4>{{ $collections->where('payment_type', 'cash')->count() }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-money-bill fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">UPI Collections</h6>
                            <h4>{{ $collections->where('payment_type', 'upi')->count() }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-mobile-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on status change
    $('#status').change(function() {
        $(this).closest('form').submit();
    });

    // Clear individual filters
    $('.btn-clear-filter').click(function() {
        const target = $(this).data('target');
        $(target).val('');
        $(this).closest('form').submit();
    });

    // Tooltip initialization
    $('[title]').tooltip();
});
</script>
@endpush
@endsection
