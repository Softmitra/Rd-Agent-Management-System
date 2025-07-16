@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">RD Account Details - {{ $account->account_number }}</h5>
                <a href="{{ route('collections.create') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Collections
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Account Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Account Number</th>
                            <td>{{ $account->account_number }}</td>
                        </tr>
                        <tr>
                            <th>Customer Name</th>
                            <td>{{ $customer->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Monthly Amount</th>
                            <td>₹{{ number_format($account->monthly_amount ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $account->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Account Details</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Opening Date</th>
                            <td>{{ $account->start_date ? $account->start_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Maturity Date</th>
                            <td>{{ $account->maturity_date ? $account->maturity_date->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Installments Paid</th>
                            <td>{{ $account->installments_paid }}</td>
                        </tr>
                        <tr>
                            <th>Total Deposited</th>
                            <td>₹{{ number_format($account->total_deposited ?? 0, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="text-muted">Recent Payments</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Receipt No</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($payments as $payment)
                                <tr>
                                    <td>{{ $payment->receipt_number }}</td>
                                    <td>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A' }}</td>
                                    <td>₹{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ ucfirst($payment->payment_method) }}</td>
                                    <td>{{ $payment->status }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No payments found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
