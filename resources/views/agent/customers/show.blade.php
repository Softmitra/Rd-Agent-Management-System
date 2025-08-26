@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Details - {{ $customer->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('agent.customers.edit', $customer) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i> Edit Customer
                        </a>
                        <a href="{{ route('agent.customers.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Basic Information</h5>
                            
                            <table class="table table-striped">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td>{{ $customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile Number:</th>
                                    <td>{{ $customer->mobile_number }}</td>
                                </tr>
                                @if($customer->phone && $customer->phone !== $customer->mobile_number)
                                <tr>
                                    <th>Alternate Phone:</th>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                                @endif
                                @if($customer->email)
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $customer->email }}</td>
                                </tr>
                                @endif
                                @if($customer->date_of_birth)
                                <tr>
                                    <th>Date of Birth:</th>
                                    <td>{{ $customer->date_of_birth->format('d M Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Agent:</th>
                                    <td>{{ $customer->agent->name ?? 'Not Assigned' }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-primary mb-3">Address & Documents</h5>
                            
                            <table class="table table-striped">
                                @if($customer->address)
                                <tr>
                                    <th width="40%">Address:</th>
                                    <td>{{ $customer->address }}</td>
                                </tr>
                                @endif
                                @if($customer->aadhar_number)
                                <tr>
                                    <th>Aadhar Number:</th>
                                    <td>{{ $customer->aadhar_number }}</td>
                                </tr>
                                @endif
                                @if($customer->pan_number)
                                <tr>
                                    <th>PAN Number:</th>
                                    <td>{{ $customer->pan_number }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Has Savings Account:</th>
                                    <td>
                                        @if($customer->has_savings_account)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($customer->savings_account_no)
                                <tr>
                                    <th>Savings Account No:</th>
                                    <td>{{ $customer->savings_account_no }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- RD Accounts Section -->
                    @if($customer->rdAccounts->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-success mb-3">RD Accounts ({{ $customer->rdAccounts->count() }})</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Account Number</th>
                                            <th>Monthly Amount</th>
                                            <th>Total Deposited</th>
                                            <th>Duration</th>
                                            <th>Interest Rate</th>
                                            <th>Maturity Amount</th>
                                            <th>Status</th>
                                            <th>Start Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->rdAccounts as $account)
                                        <tr>
                                            <td>{{ $account->account_number }}</td>
                                            <td>₹{{ number_format($account->monthly_amount, 2) }}</td>
                                            <td>₹{{ number_format($account->total_deposited, 2) }}</td>
                                            <td>{{ $account->duration_months }} months</td>
                                            <td>{{ $account->interest_rate }}%</td>
                                            <td>₹{{ number_format($account->maturity_amount, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $account->status == 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($account->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $account->start_date->format('d M Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Recent Payments Section -->
                    @if($customer->payments && $customer->payments->count() > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="text-info mb-3">Recent Payments ({{ $customer->payments->count() }})</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->payments->take(5) as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            <td>₹{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $payment->status == 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($payment->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
