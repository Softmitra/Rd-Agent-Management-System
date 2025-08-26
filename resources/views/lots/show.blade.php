@extends('layouts.app')

@section('title', 'Lot Details - ' . $lot->lot_reference_number)

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Lot Details</h5>
            <small class="text-muted">{{ $lot->lot_reference_number }}</small>
        </div>
        <div>
            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Lots
            </a>
            @if($lot->canBeModified())
                <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.edit' : 'agent.lots.edit', $lot) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit Lot
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Lot Information -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Lot Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Reference:</dt>
                                <dd class="col-sm-8">{{ $lot->lot_reference_number }}</dd>
                                
                                <dt class="col-sm-4">Date:</dt>
                                <dd class="col-sm-8">{{ $lot->lot_date->format('d/m/Y') }}</dd>
                                
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    @php
                                        $statusColors = [
                                            'draft' => 'warning',
                                            'processing' => 'info',
                                            'completed' => 'success',
                                            'verified' => 'primary'
                                        ];
                                        $color = $statusColors[$lot->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($lot->status) }}</span>
                                </dd>
                                
                                @if(auth()->user()->id == 1)
                                <dt class="col-sm-4">Agent:</dt>
                                <dd class="col-sm-8">{{ $lot->agent->name }}</dd>
                                @endif
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Total Accounts:</dt>
                                <dd class="col-sm-7"><strong>{{ $lot->total_accounts }}</strong></dd>
                                
                                <dt class="col-sm-5">Total Amount:</dt>
                                <dd class="col-sm-7"><strong>₹{{ number_format($lot->total_amount, 2) }}</strong></dd>
                                
                                <dt class="col-sm-5">Commission ({{ $lot->commission_percentage }}%):</dt>
                                <dd class="col-sm-7"><strong class="text-success">₹{{ number_format($lot->commission_amount, 2) }}</strong></dd>
                                
                                <dt class="col-sm-5">Created:</dt>
                                <dd class="col-sm-7">{{ $lot->created_at->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                    
                    @if($lot->lot_description)
                    <div class="row">
                        <div class="col-12">
                            <strong>Description:</strong>
                            <p class="mb-0">{{ $lot->lot_description }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cogs"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($lot->status == 'draft' || $lot->status == 'processing')
                        <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.finalize' : 'agent.lots.finalize', $lot) }}" class="mb-3">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-success btn-block"
                                    onclick="return confirm('Are you sure you want to finalize this lot?')">
                                <i class="fas fa-check"></i> Finalize Lot
                            </button>
                        </form>
                    @endif
                    
                    @if($lot->import_errors && count($lot->import_errors) > 0)
                        <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.download-errors' : 'agent.lots.download-errors', $lot) }}" 
                           class="btn btn-danger btn-block mb-3">
                            <i class="fas fa-download"></i> Download Errors ({{ count($lot->import_errors) }})
                        </a>
                    @endif
                    
                    @if($lot->import_file_name)
                        <div class="alert alert-info">
                            <small><strong>Import File:</strong><br>{{ $lot->import_file_name }}</small>
                        </div>
                    @endif
                    
                    @if($lot->verified_at && $lot->verifiedBy)
                        <div class="alert alert-success">
                            <small><strong>Verified by:</strong> {{ $lot->verifiedBy->name }}<br>
                            <strong>Date:</strong> {{ $lot->verified_at->format('d/m/Y H:i') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h5 text-primary">{{ $lot->collections->count() }}</div>
                                <small class="text-muted">Collections</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-right">
                                <div class="h5 text-success">₹{{ number_format($lot->total_amount, 0) }}</div>
                                <small class="text-muted">Amount</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="h5 text-warning">₹{{ number_format($lot->commission_amount, 0) }}</div>
                            <small class="text-muted">Commission</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Collections -->
    <div class="card mt-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-list"></i> Assigned Collections ({{ $lot->collections->count() }})</h6>
                @if($lot->canBeModified())
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#assignCollectionModal">
                        <i class="fas fa-plus"></i> Assign Collection
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($lot->collections->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>RD Account</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Collection Date</th>
                                <th>Receipt</th>
                                <th>Months Paid</th>
                                @if($lot->canBeModified())
                                <th>Actions</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lot->collections as $collection)
                                <tr>
                                    <td>
                                        @if($collection->rdAccount)
                                            <strong>{{ $collection->rdAccount->account_number }}</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($collection->customer)
                                            {{ $collection->customer->name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td><strong>₹{{ number_format($collection->amount, 2) }}</strong></td>
                                    <td>{{ $collection->collection_date->format('d/m/Y') }}</td>
                                    <td>{{ $collection->receipt_number ?? '-' }}</td>
                                    <td>{{ $collection->months_paid ?? 1 }}</td>
                                    @if($lot->canBeModified())
                                    <td>
                                        <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.remove-collection' : 'agent.lots.remove-collection', [$lot, $collection]) }}" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Remove this collection from the lot?')"
                                                    title="Remove from lot">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Row -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-light">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <strong>Total Collections:</strong> {{ $lot->collections->count() }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Total Amount:</strong> ₹{{ number_format($lot->collections->sum('amount'), 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Commission ({{ $lot->commission_percentage }}%):</strong> ₹{{ number_format($lot->collections->sum('amount') * $lot->commission_percentage / 100, 2) }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Net Amount:</strong> ₹{{ number_format($lot->collections->sum('amount') - ($lot->collections->sum('amount') * $lot->commission_percentage / 100), 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No Collections Assigned</h6>
                    <p class="text-muted">Start by assigning collections to this lot.</p>
                    @if($lot->canBeModified())
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#assignCollectionModal">
                            <i class="fas fa-plus"></i> Assign First Collection
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Import Errors (if any) -->
    @if($lot->import_errors && count($lot->import_errors) > 0)
    <div class="card mt-4">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Import Errors ({{ count($lot->import_errors) }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Account Number</th>
                            <th>Amount</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lot->import_errors as $error)
                            <tr>
                                <td>{{ $error['row'] ?? '-' }}</td>
                                <td>{{ $error['account_number'] ?? '-' }}</td>
                                <td>{{ isset($error['amount']) ? '₹' . number_format($error['amount'], 2) : '-' }}</td>
                                <td class="text-danger">{{ $error['error'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.download-errors' : 'agent.lots.download-errors', $lot) }}" 
                   class="btn btn-danger">
                    <i class="fas fa-download"></i> Download Error Report
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Assign Collection Modal -->
@if($lot->canBeModified())
<div class="modal fade" id="assignCollectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Collection to Lot</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.assign-collection' : 'agent.lots.assign-collection', $lot) }}">
                    @csrf
                    <div class="form-group">
                        <label for="collection_id">Available Collections</label>
                        <select name="collection_id" id="collection_id" class="form-control" required>
                            <option value="">Select a collection</option>
                            @foreach(\App\Models\Collection::with(['rdAccount', 'customer'])->notInLot()->forAgent(auth()->id())->get() as $collection)
                                <option value="{{ $collection->id }}">
                                    {{ $collection->rdAccount ? $collection->rdAccount->account_number : 'N/A' }} - 
                                    {{ $collection->customer ? $collection->customer->name : 'N/A' }} - 
                                    ₹{{ number_format($collection->amount, 2) }} 
                                    ({{ $collection->collection_date->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Only collections not assigned to any lot are shown</small>
                    </div>
                    <div class="text-right">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Assign Collection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection 