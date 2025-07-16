@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">RD Account Details</h5>
                <div>
                    <a href="{{ route('agent.rd-agent-accounts.edit', ['rd_agent_account' => $rdAccount->id]) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Account
                    </a>
                    <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted">Account Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Account Number</th>
                            <td>{{ $rdAccount->account_number }}</td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $rdAccount->status == 'active' ? 'success' : ($rdAccount->status == 'matured' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($rdAccount->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Customer Name</th>
                            <td>{{ $rdAccount->customer->name }}</td>
                        </tr>
                        <tr>
                            <th>Agent</th>
                            <td>{{ $rdAccount->agent->name }}</td>
                        </tr>
                        <tr>
                            <th>Monthly Amount</th>
                            <td>₹{{ number_format($rdAccount->monthly_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Deposited</th>
                            <td>₹{{ number_format($rdAccount->total_deposited, 2) }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-muted">Account Details</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Opening Date</th>
                            <td>{{ \Carbon\Carbon::parse($rdAccount->start_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Maturity Date</th>
                            <td>{{ \Carbon\Carbon::parse($rdAccount->maturity_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Duration (Months)</th>
                            <td>{{ $rdAccount->duration_months }}</td>
                        </tr>
                        <tr>
                            <th>Interest Rate</th>
                            <td>{{ $rdAccount->interest_rate }}%</td>
                        </tr>
                        <tr>
                            <th>Maturity Amount</th>
                            <td>₹{{ number_format($rdAccount->maturity_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Installments Paid</th>
                            <td>{{ $rdAccount->installments_paid }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($rdAccount->is_joint_account)
            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="text-muted">Joint Account Details</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">Joint Holder Name</th>
                            <td>{{ $rdAccount->joint_holder_name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif

            @if($rdAccount->note)
            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="text-muted">Notes</h6>
                    <div class="card">
                        <div class="card-body">
                            {{ $rdAccount->note }}
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row mt-4">
                <div class="col-12">
                    <h6 class="text-muted">Account Actions</h6>
                    <div class="btn-group">
                        @if($rdAccount->status == 'active')
                        <form action="{{ route('rd-agent-accounts.close', $rdAccount) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to close this account?')">
                                <i class="fas fa-times"></i> Close Account
                            </button>
                        </form>
                        @endif
                        @if($rdAccount->status == 'active' && \Carbon\Carbon::parse($rdAccount->maturity_date)->isPast())
                        <form action="{{ route('rd-agent-accounts.mature', $rdAccount) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to mark this account as matured?')">
                                <i class="fas fa-clock"></i> Mark as Matured
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
