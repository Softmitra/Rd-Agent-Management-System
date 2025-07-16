@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
<div class="container-fluid px-4">
    <h5 class="mt-4">Payment Details</h5>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
        <li class="breadcrumb-item active">Payment Details</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-money-bill-wave me-1"></i>
            Payment Information
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Receipt Number:</strong> {{ $payment->receipt_number }}</p>
                    <p><strong>Payment Date:</strong> {{ $payment->payment_date->format('d/m/Y') }}</p>
                    <p><strong>Amount:</strong> ₹{{ number_format($payment->amount, 2) }}</p>
                    <p><strong>Payment Mode:</strong> {{ ucfirst($payment->payment_mode) }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : 'warning' }}">
                            {{ ucfirst($payment->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Customer Name:</strong> {{ $payment->customer->name }}</p>
                    <p><strong>Account Number:</strong> {{ $payment->rdAccount->account_number }}</p>
                    <p><strong>Agent Name:</strong> {{ $payment->agent }}</p>
                    <p><strong>Created At:</strong> {{ $payment->created_at->format('d/m/Y H:i:s') }}</p>
                </div>
            </div>

            @if($payment->payment_mode === 'cheque')
            <div class="row mt-4">
                <div class="col-md-12">
                    <h6>Cheque Details</h6>
                    <p><strong>Cheque Number:</strong> {{ $payment->cheque_number }}</p>
                    <p><strong>Bank Name:</strong> {{ $payment->bank_name }}</p>
                </div>
            </div>
            @endif

            @if($payment->payment_mode === 'online')
            <div class="row mt-4">
                <div class="col-md-12">
                    <h6>Online Payment Details</h6>
                    <p><strong>Transaction ID:</strong> {{ $payment->transaction_id }}</p>
                </div>
            </div>
            @endif

            @if($payment->payment_mode === 'upi')
            <div class="row mt-4">
                <div class="col-md-12">
                    <h6>UPI Payment Details</h6>
                    <p><strong>UPI ID:</strong> {{ $payment->upi_id }}</p>
                </div>
            </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-primary">
                    <i class="fas fa-download me-1"></i> Download Receipt
                </a>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 