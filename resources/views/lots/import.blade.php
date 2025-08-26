@extends('layouts.app')

@section('title', 'Import Lot from Excel')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0">Import Lot from Excel</h5>
        <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Lots
        </a>
    </div>

    <!-- Instructions -->
    <div class="alert alert-info">
        <h6><i class="fas fa-info-circle"></i> Excel Import Instructions</h6>
        <p class="mb-2">Upload the Excel file downloaded from India Post system. The system will:</p>
        <ul class="mb-0 pl-4">
            <li>Create a new lot based on the Excel data</li>
            <li>Automatically match and assign collections to the lot</li>
            <li>Calculate commissions automatically (3.75%)</li>
            <li>Generate error report for any mismatched entries</li>
        </ul>
    </div>

    <!-- Required Format -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-file-excel"></i> Required Excel Format</h6>
        </div>
        <div class="card-body">
            <p><strong>Your Excel file must contain these columns:</strong></p>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Column Name</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>account_number</code></td>
                            <td>RD Account Number</td>
                            <td>RD123456789</td>
                        </tr>
                        <tr>
                            <td><code>customer_name</code></td>
                            <td>Customer Full Name</td>
                            <td>John Doe</td>
                        </tr>
                        <tr>
                            <td><code>amount</code></td>
                            <td>Collection Amount</td>
                            <td>1500.00</td>
                        </tr>
                        <tr>
                            <td><code>collection_date</code></td>
                            <td>Date of Collection</td>
                            <td>2024-01-15</td>
                        </tr>
                        <tr>
                            <td><code>receipt_number</code></td>
                            <td>Receipt Number (optional)</td>
                            <td>REC123</td>
                        </tr>
                        <tr>
                            <td><code>months_paid</code></td>
                            <td>Number of months paid</td>
                            <td>1</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route(auth()->user()->id == 1 ? 'admin.lots.import' : 'agent.lots.import') }}" enctype="multipart/form-data">
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
                                   placeholder="e.g., LOT2024001"
                                   class="form-control @error('lot_reference_number') is-invalid @enderror">
                            <small class="form-text text-muted">Unique reference for this lot</small>
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
                </div>

                <div class="form-group">
                    <label for="excel_file">Excel File *</label>
                    <div class="custom-file">
                        <input type="file" 
                               name="excel_file" 
                               id="excel_file"
                               accept=".xlsx,.xls"
                               required
                               class="custom-file-input @error('excel_file') is-invalid @enderror">
                        <label class="custom-file-label" for="excel_file">Choose Excel file...</label>
                        @error('excel_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted">Only .xlsx and .xls files are supported (Max: 5MB)</small>
                </div>

                <!-- Processing Options -->
                <div class="card bg-light mt-4">
                    <div class="card-body">
                        <h6 class="card-title"><i class="fas fa-cogs"></i> Processing Options</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_finalize" id="auto_finalize" value="1" {{ old('auto_finalize') ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_finalize">
                                Auto-finalize lot after successful import
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="skip_validation" id="skip_validation" value="1" {{ old('skip_validation') ? 'checked' : '' }}>
                            <label class="form-check-label" for="skip_validation">
                                Skip strict validation (import even with warnings)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-group text-right mt-4">
                    <a href="{{ route(auth()->user()->id == 1 ? 'admin.lots.index' : 'agent.lots.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-upload"></i> Import Lot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // File input label update
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Auto-generate lot reference
    $('#lot_date').on('change', function() {
        var date = new Date($(this).val());
        var ref = $('#lot_reference_number');
        if (ref.val() === '') {
            var dateStr = date.getFullYear() + ('0' + (date.getMonth() + 1)).slice(-2) + ('0' + date.getDate()).slice(-2);
            ref.val('LOT' + dateStr);
        }
    });
});
</script>
@endpush
@endsection 