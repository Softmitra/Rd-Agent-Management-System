@extends('layouts.app')

@section('title', 'Create New Lot')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Create New Lot</h5>
        <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Lots
        </a>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> Manual Lot Creation</h6>
        <p class="mb-0">Create a lot manually and assign collections later. You can also use the Excel import feature for bulk lot creation with automatic collection assignment.</p>
    </div>

    <!-- Create Form -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.store' : 'agent.lots.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lot_reference_number">Lot Reference Number *</label>
                            <input type="text" 
                                   name="lot_reference_number" 
                                   id="lot_reference_number"
                                   value="{{ old('lot_reference_number') }}"
                                   required
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
                                   value="{{ old('lot_date', date('Y-m-d')) }}"
                                   required
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
                           value="{{ old('lot_description') }}"
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
                               value="{{ old('commission_percentage', '3.75') }}"
                               step="0.01"
                               min="0"
                               max="100"
                               class="form-control @error('commission_percentage') is-invalid @enderror">
                        <div class="input-group-append">
                            <span class="input-group-text">%</span>
                        </div>
                        @error('commission_percentage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Default is 3.75% as per standard commission structure</small>
                </div>

                <!-- Action Buttons -->
                <div class="form-group text-right">
                    <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Lot
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Next Steps -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-list"></i> Next Steps</h6>
        </div>
        <div class="card-body">
            <p><strong>After creating the lot, you can:</strong></p>
            <ul class="mb-0">
                <li>Manually assign individual collections from the lot detail page</li>
                <li>Import an Excel file to bulk assign collections</li>
                <li>Finalize the lot when all collections are assigned</li>
                <li>Download commission reports and error logs if needed</li>
            </ul>
        </div>
    </div>
</div>
@endsection 