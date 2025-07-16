@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Create Payment</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payments.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                @if (isset($rdAccount))
                                    <div class="col-md-12 mb-3">
                                        <label for="rd_account_id" class="form-label">RD Account</label>
                                        <input type="hidden" name="rd_account_id" value="{{ $rdAccount->id }}">
                                        <input type="text" class="form-control"
                                            value="{{ $rdAccount->account_number }} - {{ $rdAccount->customer->name ?? '' }}"
                                            disabled>
                                    </div>
                                @else
                                    <div class="col-md-6 mb-3">
                                        <label for="rd_account_id" class="form-label">RD Account ID</label>
                                        <input type="number" class="form-control" name="rd_account_id" id="rd_account_id"
                                            value="{{ old('rd_account_id') }}" required>
                                    </div>
                                @endif

                                <div class="col-md-6 mb-3">
                                    <label for="amount" class="form-label">Amount</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" id="amount"
                                        value="{{ old('amount', isset($rdAccount) ? $rdAccount->monthly_amount : '') }}"
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" name="payment_date" id="payment_date"
                                        value="{{ old('payment_date', date('Y-m-d')) }}" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Payment Method</label>
                                    <select class="form-control" name="payment_method" id="payment_method" required>
                                        <option value="">Select Method</option>
                                        <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash
                                        </option>
                                        <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI
                                        </option>
                                        <option value="bank_transfer"
                                            {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                                        </option>
                                        <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>
                                            Cheque</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control" name="transaction_id" id="transaction_id"
                                        value="{{ old('transaction_id') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="remarks" class="form-label">Remarks</label>
                                    <textarea class="form-control" name="remarks" id="remarks" rows="2">{{ old('remarks') }}</textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Create Payment</button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection


@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Create Payment</h1>
    <form action="{{ route('payments.store') }}" method="POST">
        @csrf
        
        @if(isset($rdAccount))
            <div class="mb-3">
                <label for="rd_account_id" class="form-label">RD Account</label>
                <input type="hidden" name="rd_account_id" value="{{ $rdAccount->id }}">
                <input type="text" class="form-control" value="{{ $rdAccount->account_number }} - {{ $rdAccount->customer->name ?? '' }}" disabled>
            </div>
        @else
            <div class="mb-3">
                <label for="rd_account_id" class="form-label">RD Account ID</label>
                <input type="number" class="form-control" name="rd_account_id" id="rd_account_id" value="{{ old('rd_account_id') }}" required>
            </div>
        @endif

        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" id="amount" value="{{ old('amount', isset($rdAccount) ? $rdAccount->monthly_amount : '') }}" required>
        </div>

        <div class="mb-3">
            <label for="payment_date" class="form-label">Payment Date</label>
            <input type="date" class="form-control" name="payment_date" id="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
        </div>

        <div class="mb-3">
            <label for="payment_method" class="form-label">Payment Method</label>
            <select class="form-control" name="payment_method" id="payment_method" required>
                <option value="">Select Method</option>
                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="upi" {{ old('payment_method') == 'upi' ? 'selected' : '' }}>UPI</option>
                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="transaction_id" class="form-label">Transaction ID</label>
            <input type="text" class="form-control" name="transaction_id" id="transaction_id" value="{{ old('transaction_id') }}">
        </div>

        <div class="mb-3">
            <label for="remarks" class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" id="remarks" rows="2">{{ old('remarks') }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Create Payment</button>
    </form>
</div>
@endsection 