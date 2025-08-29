@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-edit"></i> Edit RD Account
                </h5>
                <a href="{{ route('agent.rd-agent-accounts.show', $rdAccount) }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Details
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('agent.rd-agent-accounts.update', $rdAccount) }}" method="POST" id="editRdAccountForm">
                @csrf
                @method('PUT')
                
                <!-- Account Status Alert -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Account Status:</strong> 
                    <span class="badge bg-{{ $rdAccount->status == 'active' ? 'success' : ($rdAccount->status == 'matured' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($rdAccount->status) }}
                    </span>
                    @if($rdAccount->status != 'active')
                        <br><small class="text-muted">Some fields may be disabled for {{ $rdAccount->status }} accounts.</small>
                    @endif
                </div>

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="account_number" class="form-label">Account Number *</label>
                                    <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                           id="account_number" name="account_number" maxlength="12"
                                           value="{{ old('account_number', $rdAccount->account_number) }}" 
                                           {{ $rdAccount->status != 'active' ? 'readonly' : '' }} required>
                                    @error('account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Account number cannot be changed for non-active accounts.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="customer_id" class="form-label">Customer *</label>
                                    <select class="form-control select2-customer @error('customer_id') is-invalid @enderror" 
                                            id="customer_id" name="customer_id" 
                                            {{ $rdAccount->status != 'active' ? 'disabled' : '' }} required>
                                        <option value="">Search and select customer...</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    data-phone="{{ $customer->mobile_number }}"
                                                    data-name="{{ $customer->name }}"
                                                    data-email="{{ $customer->email ?? '' }}"
                                                    {{ old('customer_id', $rdAccount->customer_id) == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} - {{ $customer->mobile_number }}
                                                @if($customer->email) - {{ $customer->email }} @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('customer_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="monthly_amount" class="form-label">Monthly Amount (₹) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="100" min="100" 
                                               class="form-control @error('monthly_amount') is-invalid @enderror" 
                                               id="monthly_amount" name="monthly_amount" 
                                               value="{{ old('monthly_amount', $rdAccount->monthly_amount) }}" 
                                               placeholder="Enter amount in multiples of 100"
                                               {{ $rdAccount->status != 'active' ? 'readonly' : '' }} required>
                                        <span class="input-group-text">.00</span>
                                    </div>
                                    @error('monthly_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Amount must be in multiples of ₹100.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="opening_date" class="form-label">Opening Date *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-calendar"></i></span>
                                        <input type="text" class="form-control datepicker @error('opening_date') is-invalid @enderror" 
                                               id="opening_date" name="opening_date" 
                                               value="{{ old('opening_date', \Carbon\Carbon::parse($rdAccount->start_date)->format('d/m/Y')) }}" 
                                               {{ $rdAccount->status != 'active' ? 'readonly' : '' }} required>
                                    </div>
                                    @error('opening_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Select date from calendar (last 6 years).</small>
                                </div>

                                <div class="mb-3">
                                    <label for="duration_months" class="form-label">Duration (Months) *</label>
                                    <input type="number" min="1" max="120" 
                                           class="form-control @error('duration_months') is-invalid @enderror" 
                                           id="duration_months" name="duration_months" 
                                           value="{{ old('duration_months', $rdAccount->duration_months) }}" 
                                           {{ $rdAccount->status != 'active' ? 'readonly' : '' }} required>
                                    @error('duration_months')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Duration between 1-120 months.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-cog text-primary"></i> Account Settings</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="interest_rate" class="form-label">Interest Rate (%) *</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" min="0" max="100" 
                                               class="form-control @error('interest_rate') is-invalid @enderror" 
                                               id="interest_rate" name="interest_rate" 
                                               value="{{ old('interest_rate', $rdAccount->interest_rate) }}" 
                                               {{ $rdAccount->status != 'active' ? 'readonly' : '' }} required>
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('interest_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status *</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="active" {{ old('status', $rdAccount->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="matured" {{ old('status', $rdAccount->status) == 'matured' ? 'selected' : '' }}>Matured</option>
                                        <option value="closed" {{ old('status', $rdAccount->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="is_joint_account" 
                                               name="is_joint_account" value="1" 
                                               {{ old('is_joint_account', $rdAccount->is_joint_account) ? 'checked' : '' }}
                                               {{ $rdAccount->status != 'active' ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="is_joint_account">
                                            <i class="fas fa-users"></i> Joint Account
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3 {{ old('is_joint_account', $rdAccount->is_joint_account) ? '' : 'd-none' }}" 
                                     id="joint_holder_name_container">
                                    <label for="joint_holder_name" class="form-label">Joint Holder Name</label>
                                    <input type="text" class="form-control @error('joint_holder_name') is-invalid @enderror" 
                                           id="joint_holder_name" name="joint_holder_name" 
                                           value="{{ old('joint_holder_name', $rdAccount->joint_holder_name) }}"
                                           {{ $rdAccount->status != 'active' ? 'readonly' : '' }}>
                                    @error('joint_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="registered_phone" class="form-label">Registered Phone</label>
                                    <input type="text" class="form-control @error('registered_phone') is-invalid @enderror" 
                                           id="registered_phone" name="registered_phone" 
                                           value="{{ old('registered_phone', $rdAccount->registered_phone) }}"
                                           maxlength="10" pattern="[0-9]{10}"
                                           {{ $rdAccount->status != 'active' ? 'readonly' : '' }}>
                                    @error('registered_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">10-digit mobile number.</small>
                                </div>

                                <div class="mb-3">
                                    <label for="note" class="form-label">Notes</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              id="note" name="note" rows="3" 
                                              placeholder="Enter any additional notes about this account..."
                                              {{ $rdAccount->status != 'active' ? 'readonly' : '' }}>{{ old('note', $rdAccount->note) }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Calculated Values Display -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calculator text-primary"></i> Calculated Values</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <small class="text-muted">Maturity Date</small>
                                        <div class="fw-bold" id="calculated_maturity_date">
                                            {{ \Carbon\Carbon::parse($rdAccount->maturity_date)->format('d/m/Y') }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <small class="text-muted">Maturity Amount</small>
                                        <div class="fw-bold" id="calculated_maturity_amount">
                                            ₹{{ number_format($rdAccount->maturity_amount, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agent.rd-agent-accounts.show', $rdAccount) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary" id="updateBtn">
                                    <i class="fas fa-save"></i> Update Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<!-- Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for customer dropdown
    $('.select2-customer').select2({
        theme: 'bootstrap',
        placeholder: 'Search and select customer...',
        allowClear: true,
        width: '100%'
    });

    // Initialize Datepicker
    $('.datepicker').datepicker({
        format: 'dd/mm/yyyy',
        autoclose: true,
        todayHighlight: true,
        startDate: '-6y',
        endDate: '+0d',
        todayBtn: 'linked',
        clearBtn: true,
        weekStart: 1,
        calendarWeeks: true
    });

    // Joint account toggle
    const isJointAccount = document.getElementById('is_joint_account');
    const jointHolderContainer = document.getElementById('joint_holder_name_container');
    const jointHolderInput = document.getElementById('joint_holder_name');

    isJointAccount.addEventListener('change', function() {
        if (this.checked) {
            jointHolderContainer.classList.remove('d-none');
            jointHolderInput.required = true;
        } else {
            jointHolderContainer.classList.add('d-none');
            jointHolderInput.required = false;
            jointHolderInput.value = '';
        }
    });

    // Monthly amount denomination validation
    $('#monthly_amount').on('input', function() {
        var amount = parseInt($(this).val());
        if (amount % 100 !== 0) {
            $(this).addClass('is-invalid');
            if (!$('#denomination-warning').length) {
                $(this).after('<div class="invalid-feedback denomination-warning">Amount must be in multiples of ₹100</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $('#denomination-warning').remove();
        }
    });

    // Auto-calculate maturity date and amount
    function calculateMaturity() {
        var monthlyAmount = parseFloat($('#monthly_amount').val()) || 0;
        var duration = parseInt($('#duration_months').val()) || 0;
        var interestRate = parseFloat($('#interest_rate').val()) || 0;
        var openingDate = $('#opening_date').val();

        if (monthlyAmount && duration && openingDate) {
            // Calculate maturity date
            var dateParts = openingDate.split('/');
            var startDate = new Date(dateParts[2], dateParts[1] - 1, dateParts[0]);
            var maturityDate = new Date(startDate);
            maturityDate.setMonth(maturityDate.getMonth() + duration);
            
            var maturityDateStr = maturityDate.getDate().toString().padStart(2, '0') + '/' + 
                                (maturityDate.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                                maturityDate.getFullYear();
            
            $('#calculated_maturity_date').text(maturityDateStr);

            // Calculate maturity amount
            var totalDeposits = monthlyAmount * duration;
            var interest = totalDeposits * (interestRate / 100);
            var maturityAmount = totalDeposits + interest;
            
            $('#calculated_maturity_amount').text('₹' + maturityAmount.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        }
    }

    // Trigger calculation on field changes
    $('#monthly_amount, #duration_months, #interest_rate, #opening_date').on('input change', calculateMaturity);

    // Form validation
    $('#editRdAccountForm').on('submit', function(e) {
        var monthlyAmount = parseInt($('#monthly_amount').val());
        if (monthlyAmount % 100 !== 0) {
            e.preventDefault();
            alert('Monthly amount must be in multiples of ₹100');
            $('#monthly_amount').focus();
            return false;
        }
    });

    // Disable form fields for non-active accounts
    @if($rdAccount->status != 'active')
        $('input, select, textarea').prop('readonly', true);
        $('input[type="checkbox"]').prop('disabled', true);
        $('.select2-customer').prop('disabled', true);
        $('.datepicker').prop('readonly', true);
    @endif
});
</script>

<style>
    .input-group-text {
        background-color: #f8f9fa;
        border-color: #ced4da;
        color: #495057;
        font-weight: 500;
    }
    
    .datepicker .datepicker-switch:hover,
    .datepicker .next:hover,
    .datepicker .prev:hover {
        background-color: #007bff !important;
        color: white !important;
    }
    
    .datepicker .today {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .datepicker .active {
        background-color: #007bff !important;
        color: white !important;
    }
    
    .denomination-warning {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0;
    }
    
    .select2-container--bootstrap .select2-selection {
        border-color: #ced4da;
        border-radius: 0.25rem;
    }
    
    .select2-container--bootstrap .select2-selection--single {
        height: calc(1.5em + 0.75rem + 2px);
        padding: 0.375rem 0.75rem;
    }
    
    .select2-container--bootstrap .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5;
    }
    
    .select2-container--bootstrap .select2-selection--single .select2-selection__placeholder {
        color: #6c757d;
    }
    
    .select2-container--bootstrap .select2-dropdown {
        border-color: #ced4da;
        border-radius: 0.25rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .select2-container--bootstrap .select2-results__option--highlighted[aria-selected] {
        background-color: #007bff;
        color: white;
    }
    
    .select2-container--bootstrap .select2-results__option[aria-selected=true] {
        background-color: #e9ecef;
        color: #495057;
    }
    
    .select2-container--bootstrap .select2-search--dropdown .select2-search__field {
        border-color: #ced4da;
        border-radius: 0.25rem;
    }
</style>
@endpush
@endsection
