@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h5>Customer Details</h5>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('customers.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Customer
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Customer Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">Name</th>
                                    <td>{{ $customer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $customer->email }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile Number</th>
                                    <td>{{ $customer->mobile_number }}</td>
                                </tr>
                                <tr>
                                    <th>Phone Number</th>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Date of Birth</th>
                                    <td>{{ $customer->date_of_birth->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Address</th>
                                    <td>{{ $customer->address }}</td>
                                </tr>
                                <tr>
                                    <th>Agent</th>
                                    <td>
                                        @if($customer->agent)
                                            <a href="{{ route('agents.show', $customer->agent) }}">
                                                {{ $customer->agent->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">No Agent Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- ID Documents -->
                        <!-- <div class="col-md-6">
                            <h5 class="mb-3">ID Documents</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">Aadhar Number</th>
                                    <td>{{ $customer->aadhar_number ?? 'Not Provided' }}</td>
                                </tr>
                                <tr>
                                    <th>PAN Number</th>
                                    <td>{{ $customer->pan_number ?? 'Not Provided' }}</td>
                                </tr>
                                <tr>
                                    <th>Aadhar Card</th>
                                    <td>
                                        @if($customer->aadhar_file)
                                            <a href="{{ Storage::url($customer->aadhar_file) }}" target="_blank">
                                                View Document
                                            </a>
                                        @else
                                            Not Uploaded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>PAN Card</th>
                                    <td>
                                        @if($customer->pan_file)
                                            <a href="{{ Storage::url($customer->pan_file) }}" target="_blank">
                                                View Document
                                            </a>
                                        @else
                                            Not Uploaded
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Photo</th>
                                    <td>
                                        @if($customer->photo)
                                            <img src="{{ Storage::url($customer->photo) }}" 
                                                 alt="Customer Photo" 
                                                 style="max-width: 100px; max-height: 100px;">
                                        @else
                                            Not Uploaded
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mt-4 mb-3">Savings Account</h5>
                            <table class="table table-bordered">
                                <tr>
                                    <th style="width: 200px;">Has Savings Account</th>
                                    <td>
                                        @if($customer->has_savings_account)
                                            <span class="badge badge-success">Yes</span>
                                        @else
                                            <span class="badge badge-danger">No</span>
                                        @endif
                                    </td>
                                </tr>
                                @if($customer->has_savings_account)
                                <tr>
                                    <th>CIF ID</th>
                                    <td>{{ $customer->cif_id }}</td>
                                </tr>
                                <tr>
                                    <th>Account Number</th>
                                    <td>{{ $customer->savings_account_no }}</td>
                                </tr>
                                @endif
                            </table>
                        </div> -->
                    </div>

                    <!-- RD Accounts -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">RD Accounts</h5>
                            @if($customer->rdAccounts->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Account Number</th>
                                                <th>Monthly Amount</th>
                                                <th>Duration</th>
                                                <th>Start Date</th>
                                                <th>Maturity Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customer->rdAccounts as $account)
                                                <tr>
                                                    <td>{{ $account->account_number }}</td>
                                                    <td>₹{{ number_format($account->monthly_amount, 2) }}</td>
                                                    <td>{{ $account->duration_months }} months</td>
                                                    <td>{{ $account->start_date->format('d M Y') }}</td>
                                                    <td>{{ $account->maturity_date->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $account->status === 'active' ? 'success' : ($account->status === 'matured' ? 'info' : 'danger') }}">
                                                            {{ ucfirst($account->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('admin.rd-accounts.show', $account) }}"
                                                           class="btn btn-sm btn-info"
                                                           title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    No RD accounts found for this customer.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
