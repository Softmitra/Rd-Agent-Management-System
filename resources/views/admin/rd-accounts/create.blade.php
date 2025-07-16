@extends('layouts.app')

@section('title', 'Create RD Account')

@section('content')
    <style>
        .vertical-center {
            display: flex;
            align-items: center;
        }
    </style>
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5>Create New RD Account</h5>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('admin.rd-accounts.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('admin.rd-accounts.store') }}" method="POST" id="rdAccountForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="agent_id">Agent *</label>
                                        <select class="form-control" id="agent_id" name="agent_id" required>
                                            <option value="">Select Agent</option>
                                            @foreach ($agents as $agent)
                                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer_id">Customer *</label>
                                        <select class="form-control" id="customer_id" name="customer_id" required>
                                            <option value="">Select Customer</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="account_number">Account Number *</label>
                                        <input type="text" class="form-control" id="account_number"
                                            placeholder="Enter Account number" name="account_number"
                                            value="{{ old('account_number') }}" required>
                                        <small class="text-muted">Enter a unique account number</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="registered_phone">Registered Phone *</label>
                                        <input type="text" class="form-control" id="registered_phone"
                                            name="registered_phone" value="{{ old('registered_phone') }}" required
                                            maxlength="10" pattern="[0-9]{10}">
                                        <small class="text-muted">Enter 10 digit phone number</small>
                                    </div>
                                </div>
                                <div class="col-md-6 vertical-center">
                                    <div class="form-group">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="is_joint_account"
                                                name="is_joint_account" value="1"
                                                {{ old('is_joint_account') ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="is_joint_account">Is Joint
                                                Account?</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6" style="display: none;">

                                    <div class="form-group joint-holder-section">
                                        <label for="joint_holder_name">Joint Holder Name *</label>
                                        <input type="text" class="form-control" id="joint_holder_name"
                                            name="joint_holder_name" value="{{ old('joint_holder_name') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="opening_date">Opening Date *</label>
                                        <input type="text" class="form-control datepicker" id="opening_date"
                                            name="opening_date" value="{{ old('opening_date', date('d/m/Y')) }}" required
                                            placeholder="dd/mm/yyyy">
                                        <small class="text-muted">Please select date using the date picker</small>
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="monthly_amount">Monthly Amount *</label>
                                        <input type="number" class="form-control" id="monthly_amount" name="monthly_amount"
                                            value="{{ old('monthly_amount') }}" required min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="total_deposited">Total Deposited Amount *</label>
                                        <input type="number" class="form-control" id="total_deposited"
                                            name="total_deposited" value="{{ old('total_deposited', 0) }}" required
                                            min="0" step="0.01">
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="note">Note</label>
                                        <textarea class="form-control" id="note" name="note" rows="3">{{ old('note') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="form-group">
                                        <label for="paid_months">Installments Paid(Monthly)</label>
                                        <input type="number" class="form-control" id="paid_months" readonly>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Create Account</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true
            });

            // Calculate paid months when total deposited or monthly amount changes
            $('#total_deposited, #monthly_amount').on('input', function() {
                calculatePaidMonths();
            });

            function calculatePaidMonths() {
                var totalDeposited = parseFloat($('#total_deposited').val()) || 0;
                var monthlyAmount = parseFloat($('#monthly_amount').val()) || 1;

                if (monthlyAmount > 0) {
                    var paidMonths = Math.floor(totalDeposited / monthlyAmount);
                    $('#paid_months').val(paidMonths);
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

            // Calculate initial value
            calculatePaidMonths();

            // Handle customer selection to load agent

        });
        $('#agent_id').on('change', function() {
            let agentId = $(this).val();
            if (agentId) {
                $.ajax({
                    url: '/agents/' + agentId + '/customers',
                    type: 'GET',
                    success: function(customers) {
                        let options = '<option value="">Select Customer</option>';
                        customers.forEach(function(customer) {
                            options +=
                                `<option value="${customer.id}">${customer.name}</option>`;
                        });
                        $('#customer_id').html(options);
                    },
                    error: function() {
                        alert('Could not fetch customers.');
                    }
                });
            } else {
                $('#customer_id').html('<option value="">Select Customer</option>');
            }
        });
    </script>
@endpush
