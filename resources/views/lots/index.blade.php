@extends('layouts.app')

@section('title', 'Lot Management')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Lot Management</h5>
        <div>
            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.import' : 'agent.lots.import') }}" class="btn btn-success">
                <i class="fas fa-upload"></i> Import Lot
            </a>
            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.create' : 'agent.lots.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Lot
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">Date From</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">Date To</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="form-control">
                    </div>
                    @if(auth()->user()->id == 1)
                    <div class="col-md-3">
                        <label for="agent_id" class="form-label">Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control">
                            <option value="">All Agents</option>
                            @foreach(\App\Models\Agent::all() as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Summary -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="text-muted">
                Showing {{ $lots->firstItem() ?? 0 }} to {{ $lots->lastItem() ?? 0 }} 
                of {{ $lots->total() }} results
            </span>
        </div>
    </div>

    <!-- Lots Table -->
    <div class="card">
        <div class="card-body">
            @if($lots->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Lot Reference</th>
                                <th>Date</th>
                                @if(auth()->user()->id == 1)
                                <th>Agent</th>
                                @endif
                                <th>Accounts</th>
                                <th>Amount</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lots as $lot)
                                <tr>
                                    <td>
                                        <strong>{{ $lot->lot_reference_number }}</strong>
                                        @if($lot->lot_description)
                                            <br><small class="text-muted">{{ $lot->lot_description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $lot->lot_date->format('d/m/Y') }}</td>
                                    @if(auth()->user()->id == 1)
                                    <td>{{ $lot->agent->name }}</td>
                                    @endif
                                    <td>{{ $lot->total_accounts }} accounts</td>
                                    <td><strong>₹{{ number_format($lot->total_amount, 2) }}</strong></td>
                                    <td>
                                        <strong>₹{{ number_format($lot->commission_amount, 2) }}</strong>
                                        <br><small class="text-muted">{{ $lot->commission_percentage }}%</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'draft' => 'warning',
                                                'processing' => 'info',
                                                'completed' => 'success',
                                                'verified' => 'primary'
                                            ];
                                            $color = $statusColors[$lot->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">
                                            {{ ucfirst($lot->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.show' : 'agent.lots.show', $lot) }}" 
                                               class="btn btn-sm btn-outline-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($lot->canBeModified())
                                                <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.edit' : 'agent.lots.edit', $lot) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Edit Lot">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            
                                            @if($lot->import_errors && count($lot->import_errors) > 0)
                                                <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.download-errors' : 'agent.lots.download-errors', $lot) }}" 
                                                   class="btn btn-sm btn-outline-danger" title="Download Errors">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                </a>
                                            @endif
                                            
                                            @if($lot->status == 'draft' || $lot->status == 'processing')
                                                <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.finalize' : 'agent.lots.finalize', $lot) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Finalize Lot"
                                                            onclick="return confirm('Are you sure you want to finalize this lot?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($lots->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $lots->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-box fa-3x mb-3"></i>
                        <h5>No Lots Found</h5>
                        <p>Get started by creating your first lot or importing from Excel.</p>
                        <div class="mt-3">
                            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.create' : 'agent.lots.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Your First Lot
                            </a>
                            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.import' : 'agent.lots.import') }}" class="btn btn-success ml-2">
                                <i class="fas fa-upload"></i> Import From Excel
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on status change
    $('#status').change(function() {
        $(this).closest('form').submit();
    });

    // Tooltip initialization
    $('[title]').tooltip();
});
</script>
@endpush
@endsection 