@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-account-book"></i> RD Account Details
                </h5>
                <div>
                    <a href="{{ route('agent.rd-agent-accounts.edit', $rdAccount) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit"></i> Edit Account
                    </a>
                    <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Account Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Monthly Amount</h6>
                            <h4 class="mb-0">₹{{ number_format($rdAccount->monthly_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total Deposited</h6>
                            <h4 class="mb-0">₹{{ number_format($rdAccount->total_deposited, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Maturity Amount</h6>
                            <h4 class="mb-0">₹{{ number_format($rdAccount->maturity_amount, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Installments Paid</h6>
                            <h4 class="mb-0">{{ $rdAccount->installments_paid }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Rebate Amount</h6>
                            <h4 class="mb-0">₹{{ number_format($rdAccount->calculateRebate(), 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h6 class="card-title">Total with Rebate</h6>
                            <h4 class="mb-0">₹{{ number_format($rdAccount->total_amount_with_rebate, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Status Badge -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <span class="badge bg-{{ $rdAccount->status == 'active' ? 'success' : ($rdAccount->status == 'matured' ? 'warning' : 'secondary') }} fs-5 px-4 py-2">
                            <i class="fas fa-{{ $rdAccount->status == 'active' ? 'check-circle' : ($rdAccount->status == 'matured' ? 'clock' : 'times-circle') }}"></i>
                            {{ ucfirst($rdAccount->status) }} Account
                        </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Account Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Account Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-muted">Account Number</th>
                                    <td><strong>{{ $rdAccount->account_number }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Customer Name</th>
                                    <td>
                                        <strong>{{ $rdAccount->customer->name }}</strong>
                                        <br><small class="text-muted">{{ $rdAccount->customer->mobile_number }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Agent</th>
                                    <td><strong>{{ $rdAccount->agent->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Interest Rate</th>
                                    <td><strong>{{ $rdAccount->interest_rate }}%</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Duration</th>
                                    <td><strong>{{ $rdAccount->duration_months }} months</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Account Dates -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt text-primary"></i> Important Dates</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-muted">Opening Date</th>
                                    <td><strong>{{ \Carbon\Carbon::parse($rdAccount->start_date)->format('d/m/Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Maturity Date</th>
                                    <td>
                                        <strong>{{ \Carbon\Carbon::parse($rdAccount->maturity_date)->format('d/m/Y') }}</strong>
                                        @if(\Carbon\Carbon::parse($rdAccount->maturity_date)->isPast())
                                            <span class="badge bg-warning ms-2">Overdue</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Created On</th>
                                    <td><strong>{{ \Carbon\Carbon::parse($rdAccount->created_at)->format('d/m/Y H:i') }}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Last Updated</th>
                                    <td><strong>{{ \Carbon\Carbon::parse($rdAccount->updated_at)->format('d/m/Y H:i') }}</strong></td>
                                </tr>
                                @if($rdAccount->registered_phone)
                                <tr>
                                    <th class="text-muted">Registered Phone</th>
                                    <td><strong>{{ $rdAccount->registered_phone }}</strong></td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Joint Account Details -->
            @if($rdAccount->is_joint_account)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-users text-warning"></i> Joint Account Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Joint Holder Name:</strong> {{ $rdAccount->joint_holder_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Notes Section -->
            @if($rdAccount->note)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-sticky-note text-primary"></i> Notes</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">{{ $rdAccount->note }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Account Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cogs text-primary"></i> Account Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2 flex-wrap">
                                @if($rdAccount->status == 'active')
                                <form action="{{ route('agent.rd-agent-accounts.close', $rdAccount) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to close this account? This action cannot be undone.')">
                                        <i class="fas fa-times"></i> Close Account
                                    </button>
                                </form>
                                @endif
                                
                                @if($rdAccount->status == 'active' && \Carbon\Carbon::parse($rdAccount->maturity_date)->isPast())
                                <form action="{{ route('agent.rd-agent-accounts.mature', $rdAccount) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to mark this account as matured?')">
                                        <i class="fas fa-clock"></i> Mark as Matured
                                    </button>
                                </form>
                                @endif
                                
                                <a href="{{ route('agent.rd-agent-accounts.edit', $rdAccount) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Account
                                </a>
                                
                                <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-list"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar for Account Completion -->
            @if($rdAccount->status == 'active')
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-line text-primary"></i> Account Progress</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $progress = ($rdAccount->installments_paid / $rdAccount->duration_months) * 100;
                                $remaining = $rdAccount->duration_months - $rdAccount->installments_paid;
                                $rebateInfo = $rdAccount->rebate_info;
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Progress: {{ $rdAccount->installments_paid }}/{{ $rdAccount->duration_months }} installments</span>
                                    <span>{{ number_format($progress, 1) }}%</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progress }}%" 
                                         aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ number_format($progress, 1) }}%
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Rebate Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading"><i class="fas fa-gift"></i> Rebate Information</h6>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Rebate Threshold:</strong> {{ $rebateInfo['rebate_threshold'] }} months
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Rebate Amount:</strong> ₹{{ number_format($rebateInfo['rebate_amount'], 2) }}
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Status:</strong> 
                                                @if($rebateInfo['rebate_applied'])
                                                    <span class="badge bg-success">Rebate Applied</span>
                                                @else
                                                    <span class="badge bg-warning">{{ $rebateInfo['rebate_remaining'] }} months to rebate</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Remaining Installments</h6>
                                        <h4 class="text-warning">{{ $remaining }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Remaining Amount</h6>
                                        <h4 class="text-info">₹{{ number_format($remaining * $rdAccount->monthly_amount, 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Total with Rebate</h6>
                                        <h4 class="text-success">₹{{ number_format($rebateInfo['total_amount_with_rebate'], 2) }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted">Days to Maturity</h6>
                                        <h4 class="text-primary">{{ \Carbon\Carbon::parse($rdAccount->maturity_date)->diffInDays(now()) }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
