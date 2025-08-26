@extends('layouts.app')

@section('title', 'Edit Lot - ' . $lot->lot_reference_number)

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="mb-0">Edit Lot</h5>
            <small class="text-muted">{{ $lot->lot_reference_number }}</small>
        </div>
        <div>
            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.show' : 'agent.lots.show', $lot) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Lot
            </a>
            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-list"></i> All Lots
            </a>
        </div>
    </div>

    <!-- Restrictions Notice -->
    @if(!$lot->canBeModified())
    <div class="alert alert-warning">
        <h6><i class="fas fa-exclamation-triangle"></i> Limited Editing</h6>
        <p class="mb-0">This lot has status "{{ $lot->status }}" and can only have limited fields edited. Collections cannot be modified.</p>
    </div>
    @endif

    <div class="row">
        <!-- Edit Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-edit"></i> Edit Lot Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.update' : 'agent.lots.update', $lot) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lot_reference_number">Lot Reference Number *</label>
                                    <input type="text" 
                                           name="lot_reference_number" 
                                           id="lot_reference_number"
                                           value="{{ old('lot_reference_number', $lot->lot_reference_number) }}"
                                           required
                                           {{ !$lot->canBeModified() ? 'readonly' : '' }}
                                           class="form-control @error('lot_reference_number') is-invalid @enderror">
                                    <small class="form-text text-muted">Unique reference number for this lot</small>
                                    @error('lot_reference_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lot_date">Lot Date *</label>
                                    <input type="date" 
                                           name="lot_date" 
                                           id="lot_date"
                                           value="{{ old('lot_date', $lot->lot_date->format('Y-m-d')) }}"
                                           required
                                           {{ !$lot->canBeModified() ? 'readonly' : '' }}
                                           class="form-control @error('lot_date') is-invalid @enderror">
                                    @error('lot_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lot_description">Lot Description</label>
                            <input type="text" 
                                   name="lot_description" 
                                   id="lot_description"
                                   value="{{ old('lot_description', $lot->lot_description) }}"
                                   placeholder="Optional description for this lot"
                                   class="form-control @error('lot_description') is-invalid @enderror">
                            @error('lot_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="commission_percentage">Commission Percentage</label>
                            <div class="input-group">
                                <input type="number" 
                                       name="commission_percentage" 
                                       id="commission_percentage"
                                       value="{{ old('commission_percentage', $lot->commission_percentage) }}"
                                       step="0.01"
                                       min="0"
                                       max="100"
                                       {{ !$lot->canBeModified() ? 'readonly' : '' }}
                                       class="form-control @error('commission_percentage') is-invalid @enderror">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('commission_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="form-text text-muted">Commission will be automatically recalculated based on total amount</small>
                        </div>

                        @if($lot->import_file_name)
                        <div class="form-group">
                            <label>Import File</label>
                            <div class="input-group">
                                <input type="text" value="{{ $lot->import_file_name }}" class="form-control" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text"><i class="fas fa-file-excel text-success"></i></span>
                                </div>
                            </div>
                            <small class="form-text text-muted">Original import file name (cannot be changed)</small>
                        </div>
                        @endif

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3"
                                      placeholder="Add any additional notes about this lot..."
                                      class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $lot->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-group text-right">
                            <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.show' : 'agent.lots.show', $lot) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Lot
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lot Summary -->
        <div class="col-lg-4">
            <!-- Current Status -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Current Status</h6>
                </div>
                <div class="card-body">
                    <dl>
                        <dt>Status:</dt>
                        <dd>
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
                        
                        <dt>Agent:</dt>
                        <dd>{{ $lot->agent->name }}</dd>
                        
                        <dt>Created:</dt>
                        <dd>{{ $lot->created_at->format('d/m/Y H:i') }}</dd>
                        
                        @if($lot->updated_at != $lot->created_at)
                        <dt>Last Updated:</dt>
                        <dd>{{ $lot->updated_at->format('d/m/Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Current Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-2">
                            <div class="border-bottom pb-2">
                                <div class="h5 text-primary">{{ $lot->collections->count() }}</div>
                                <small class="text-muted">Collections</small>
                            </div>
                        </div>
                        <div class="col-12 mb-2">
                            <div class="border-bottom pb-2">
                                <div class="h5 text-success">₹{{ number_format($lot->total_amount, 2) }}</div>
                                <small class="text-muted">Total Amount</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="h5 text-warning">₹{{ number_format($lot->commission_amount, 2) }}</div>
                            <small class="text-muted">Commission ({{ $lot->commission_percentage }}%)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Important Notes</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 pl-4">
                        <li class="mb-2">Changing commission percentage will recalculate commission amounts</li>
                        <li class="mb-2">Finalized lots have limited editing capabilities</li>
                        <li class="mb-2">Import file name cannot be changed after creation</li>
                        @if($lot->collections->count() > 0)
                        <li class="mb-2">This lot has {{ $lot->collections->count() }} assigned collections</li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Quick Actions -->
            @if($lot->canBeModified())
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($lot->status == 'draft' || $lot->status == 'processing')
                        <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.finalize' : 'agent.lots.finalize', $lot) }}" class="mb-2">
                            @csrf
                            <button type="submit" 
                                    class="btn btn-success btn-block btn-sm"
                                    onclick="return confirm('Are you sure you want to finalize this lot? You won\'t be able to make major changes after this.')">
                                <i class="fas fa-check"></i> Finalize Lot
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.show' : 'agent.lots.show', $lot) }}" class="btn btn-info btn-block btn-sm">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-calculate commission when percentage changes
    $('#commission_percentage').on('input', function() {
        var percentage = parseFloat($(this).val()) || 0;
        var totalAmount = {{ $lot->total_amount }};
        var commission = (totalAmount * percentage / 100).toFixed(2);
        
        // Update the display (you could add a preview somewhere)
        console.log('New commission would be: ₹' + commission);
    });
    
    // Confirm navigation away if form is dirty
    var formChanged = false;
    $('form input, form textarea, form select').on('change', function() {
        formChanged = true;
    });
    
    $('a[href]').on('click', function(e) {
        if (formChanged) {
            if (!confirm('You have unsaved changes. Are you sure you want to leave?')) {
                e.preventDefault();
            }
        }
    });
    
    // Reset form changed flag on submit
    $('form').on('submit', function() {
        formChanged = false;
    });
});
</script>
@endpush
@endsection 