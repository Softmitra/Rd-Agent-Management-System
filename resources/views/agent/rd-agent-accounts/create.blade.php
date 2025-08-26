@extends('layouts.app')

@section('title', 'Create RD Account')

@push('styles')
<!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h5>Create New RD Account</h5>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('agent.rd-agent-accounts.store') }}" method="POST" id="rdAccountForm">
                        @csrf

                        <div class="form-group">
                            <label for="customer_id">Customer *</label>
                            <select class="form-control select2-customer @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">Search and select customer...</option>
                                @forelse($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-phone="{{ $customer->mobile_number }}"
                                            data-name="{{ $customer->name }}"
                                            data-email="{{ $customer->email ?? '' }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} - {{ $customer->mobile_number }} 
                                        @if($customer->email) - {{ $customer->email }} @endif
                                    </option>
                                @empty
                                    <option value="" disabled>No customers assigned to you yet</option>
                                @endforelse
                            </select>
                            @error('customer_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                            @if($customers->isEmpty())
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    You need to have customers assigned to create RD accounts. Please contact admin.
                                </small>
                            @else
                                <small class="text-muted">
                                    <i class="fas fa-search text-primary"></i> 
                                    Type to search customers by name, phone, or email
                                </small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="account_number">Account Number *</label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror" id="account_number" name="account_number" value="{{ old('account_number') }}" required>
                            <small class="text-muted">Enter a unique account number</small>
                            @error('account_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="registered_phone">Registered Phone</label>
                            <input type="text" class="form-control @error('registered_phone') is-invalid @enderror" id="registered_phone" name="registered_phone" value="{{ old('registered_phone') }}" maxlength="10" pattern="[0-9]{10}">
                            <small class="text-muted">
                                <i class="fas fa-info-circle text-info"></i> 
                                Leave blank to auto-use customer's phone number
                            </small>
                            @error('registered_phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_joint_account" name="is_joint_account" value="1" {{ old('is_joint_account') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_joint_account">Is Joint Account?</label>
                            </div>
                        </div>

                        <div class="form-group joint-holder-section" style="display: none;">
                            <label for="joint_holder_name">Joint Holder Name *</label>
                            <input type="text" class="form-control @error('joint_holder_name') is-invalid @enderror" id="joint_holder_name" name="joint_holder_name" value="{{ old('joint_holder_name') }}">
                            @error('joint_holder_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="opening_date">Opening Date *</label>
                            <div class="input-group">
                                <input type="text" class="form-control datepicker @error('opening_date') is-invalid @enderror" id="opening_date" name="opening_date" value="{{ old('opening_date', date('d/m/Y')) }}" required placeholder="dd/mm/yyyy" readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-calendar-alt"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar text-primary"></i> 
                                Click on the calendar icon to select date
                            </small>
                            @error('opening_date')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="monthly_amount">Monthly Amount *(Denomination) *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="text" class="form-control @error('monthly_amount') is-invalid @enderror" id="monthly_amount" name="monthly_amount" value="{{ old('monthly_amount') }}" required placeholder="Enter amount in multiples of 100" pattern="[0-9]+" inputmode="numeric">
                                <div class="input-group-append">
                                    <span class="input-group-text">.00</span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-coins text-warning"></i> 
                                Amount should be in multiples of ₹100 (Minimum: ₹100)
                            </small>
                            @error('monthly_amount')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="total_deposited">Total Deposited Amount *</label>
                            <input type="number" class="form-control @error('total_deposited') is-invalid @enderror" id="total_deposited" name="total_deposited" value="{{ old('total_deposited', 0) }}" required min="0" step="0.01">
                            @error('total_deposited')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="note">Note</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                            @error('note')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="installments_paid">Installments Paid *</label>
                            <input type="number" class="form-control @error('installments_paid') is-invalid @enderror" id="installments_paid" name="installments_paid" value="{{ old('installments_paid', 0) }}" min="0" step="1">
                            <small class="text-muted">
                                <i class="fas fa-info-circle text-info"></i> 
                                Number of installments already paid (auto-calculated from total deposited)
                            </small>
                            @error('installments_paid')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" {{ $customers->isEmpty() ? 'disabled' : '' }}>
                                <i class="fas fa-save"></i> Create Account
                            </button>
                            <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Bootstrap Datepicker JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for customer dropdown with search
        $('.select2-customer').select2({
            theme: 'bootstrap',
            placeholder: 'Search and select customer...',
            allowClear: true,
            width: '100%',
            templateResult: formatCustomerOption,
            templateSelection: formatCustomerSelection,
            escapeMarkup: function(markup) { return markup; }
        });

        // Enhanced Datepicker with calendar view (last 6 years)
        $('.datepicker').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            orientation: 'bottom auto',
            container: 'body',
            startDate: '-6y',
            endDate: '+0d',
            todayBtn: 'linked',
            clearBtn: true,
            weekStart: 1,
            daysOfWeekHighlighted: "0,6",
            calendarWeeks: true,
            showOnFocus: true
        });

        // Custom formatting for Select2 customer options
        function formatCustomerOption(customer) {
            if (!customer.id) {
                return customer.text;
            }
            
            var $customer = $(customer.element);
            var name = $customer.data('name') || '';
            var phone = $customer.data('phone') || '';
            var email = $customer.data('email') || '';
            
            var $result = $(
                '<div class="select2-customer-option">' +
                    '<div class="customer-name"><i class="fas fa-user text-primary"></i> <strong>' + name + '</strong></div>' +
                    '<div class="customer-details">' +
                        '<span class="customer-phone"><i class="fas fa-phone text-success"></i> ' + phone + '</span>' +
                        (email ? '<span class="customer-email"><i class="fas fa-envelope text-info"></i> ' + email + '</span>' : '') +
                    '</div>' +
                '</div>'
            );
            
            return $result;
        }

        // Custom formatting for selected customer
        function formatCustomerSelection(customer) {
            if (!customer.id) {
                return customer.text;
            }
            
            var $customer = $(customer.element);
            var name = $customer.data('name') || customer.text;
            var phone = $customer.data('phone') || '';
            
            return name + (phone ? ' - ' + phone : '');
        }

        // Auto-populate registered phone from customer selection (Select2 compatible)
        $('#customer_id').on('select2:select', function(e) {
            var selectedData = e.params.data;
            var selectedOption = $(selectedData.element);
            var customerPhone = selectedOption.data('phone');
            
            // Only auto-fill if registered phone is empty
            if ($('#registered_phone').val() === '' && customerPhone) {
                $('#registered_phone').val(customerPhone);
            }
        });

        // Clear registered phone when customer is cleared
        $('#customer_id').on('select2:clear', function(e) {
            $('#registered_phone').val('');
        });

        // Monthly amount denomination validation and numeric input only
        $('#monthly_amount').on('input', function(e) {
            // Allow only numeric input
            var value = this.value.replace(/[^0-9]/g, '');
            this.value = value;
            
            var amount = parseFloat(value) || 0;
            
            // Auto-correct to nearest denomination on blur
            if (amount > 0) {
                calculateInstallmentsPaid();
            }
        });
        
        $('#monthly_amount').on('blur', function() {
            var amount = parseFloat($(this).val()) || 0;
            
            if (amount > 0) {
                var remainder = amount % 100;
                
                if (remainder !== 0) {
                    // Round to nearest 100
                    var roundedAmount = Math.round(amount / 100) * 100;
                    if (roundedAmount < 100) roundedAmount = 100; // Minimum 100
                    $(this).val(roundedAmount);
                    
                    // Show warning
                    showDenominationWarning(roundedAmount);
                }
                
                calculateInstallmentsPaid();
            }
        });

        function showDenominationWarning(roundedAmount) {
            // Remove existing warning
            $('.denomination-warning').remove();
            
            // Add warning message
            $('#monthly_amount').closest('.form-group').append(
                '<div class="alert alert-warning alert-sm denomination-warning mt-2">' +
                '<i class="fas fa-exclamation-triangle"></i> ' +
                'Amount adjusted to ₹' + roundedAmount + ' (nearest ₹100 denomination)' +
                '</div>'
            );
            
            // Auto-hide warning after 3 seconds
            setTimeout(function() {
                $('.denomination-warning').fadeOut();
            }, 3000);
        }

        // Calculate installments paid when total deposited or monthly amount changes
        $('#total_deposited').on('input', function() {
            calculateInstallmentsPaid();
        });
        
        // Allow manual editing of installments paid
        $('#installments_paid').on('input', function() {
            var installmentsPaid = parseInt($(this).val()) || 0;
            var monthlyAmount = parseFloat($('#monthly_amount').val()) || 0;
            
            if (monthlyAmount > 0) {
                var calculatedTotal = installmentsPaid * monthlyAmount;
                $('#total_deposited').val(calculatedTotal.toFixed(2));
            }
        });

        function calculateInstallmentsPaid() {
            var totalDeposited = parseFloat($('#total_deposited').val()) || 0;
            var monthlyAmount = parseFloat($('#monthly_amount').val()) || 1;
            
            if (monthlyAmount > 0) {
                var installmentsPaid = Math.floor(totalDeposited / monthlyAmount);
                $('#installments_paid').val(installmentsPaid);
            }
        }

        // Handle joint account section visibility
        $('#is_joint_account').change(function() {
            $('.joint-holder-section').toggle(this.checked);
            if (!this.checked) {
                $('#joint_holder_name').val('');
            }
        });

        // Set initial state of joint holder section
        if ($('#is_joint_account').is(':checked')) {
            $('.joint-holder-section').show();
        }

        // Auto-populate phone for initially selected customer (if any) - Select2 compatible
        var initialCustomerId = $('#customer_id').val();
        if (initialCustomerId && $('#registered_phone').val() === '') {
            var selectedOption = $('#customer_id').find('option:selected');
            var customerPhone = selectedOption.data('phone');
            if (customerPhone) {
                $('#registered_phone').val(customerPhone);
            }
        }

        // Calculate initial value
        calculateInstallmentsPaid();
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
    
    .input-group .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .text-info {
        color: #17a2b8 !important;
    }
    
    .text-warning {
        color: #ffc107 !important;
    }
    
    .text-primary {
        color: #007bff !important;
    }

    /* Select2 Custom Styling */
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
    
    .select2-customer-option {
        padding: 8px 0;
    }
    
    .select2-customer-option .customer-name {
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .select2-customer-option .customer-details {
        font-size: 12px;
        color: #6c757d;
    }
    
    .select2-customer-option .customer-phone,
    .select2-customer-option .customer-email {
        margin-right: 15px;
        display: inline-block;
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
    
    /* Calendar improvements */
    .datepicker table tr td.day:hover {
        background-color: #e9ecef;
        cursor: pointer;
    }
    
    .datepicker .cw {
        color: #6c757d;
        font-size: 10px;
    }
</style>
@endpush
