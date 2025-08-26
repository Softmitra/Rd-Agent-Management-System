@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-2">RD Accounts</h5>
                    <div class="d-flex gap-3 mb-2">
                        <div class="badge bg-primary fs-6 p-2">
                            <i class="fas fa-credit-card me-1"></i>
                            Total: {{ $totalRdAccounts ?? 0 }}
                        </div>
                        <div class="badge bg-success fs-6 p-2">
                            <i class="fas fa-check-circle me-1"></i>
                            Complete: {{ $completeRdAccounts ?? 0 }}
                        </div>
                        <div class="badge bg-warning fs-6 p-2">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Incomplete: {{ $incompleteRdAccounts ?? 0 }}
                        </div>
                    </div>
                </div>
                <div>
                    @if($incompleteRdAccounts > 0)
                        <button type="button" class="btn btn-warning me-2" onclick="showIncompleteRdAccounts()">
                            <i class="fas fa-exclamation-triangle"></i>
                            Complete {{ $incompleteRdAccounts }} Accounts
                        </button>
                    @endif
                    <a href="{{ route('agent.rd-agent-accounts.create') }}" class="btn btn-primary">Create New Account</a>
                    <a href="{{ route('agent.rd-agent-accounts.index') }}?export=1" class="btn btn-secondary">
                        <i class="fas fa-file-excel"></i> Export to Excel
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <form action="{{ route('agent.rd-agent-accounts.index') }}" method="GET">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search account number...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="matured" {{ request('status') == 'matured' ? 'selected' : '' }}>Matured</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Min Amount</label>
                        <input type="number" name="min_amount" class="form-control" value="{{ request('min_amount') }}" placeholder="Min amount...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Max Amount</label>
                        <input type="number" name="max_amount" class="form-control" value="{{ request('max_amount') }}" placeholder="Max amount...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('agent.rd-agent-accounts.index') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-white">
                                    <h6 class="text-uppercase mb-2">Total Accounts</h6>
                                    <h2 class="mb-0">{{ $summary['total_accounts'] }}</h2>
                                </div>
                                <div class="text-white">
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-white">
                                    <h6 class="text-uppercase mb-2">Active Accounts</h6>
                                    <h2 class="mb-0">{{ $summary['status_counts']['active'] }}</h2>
                                </div>
                                <div class="text-white">
                                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-white">
                                    <h6 class="text-uppercase mb-2">Total Monthly Deposits</h6>
                                    <h2 class="mb-0">₹{{ number_format($summary['total_monthly_deposits'], 2) }}</h2>
                                </div>
                                <div class="text-white">
                                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-white">
                                    <h6 class="text-uppercase mb-2">Matured Accounts</h6>
                                    <h2 class="mb-0">{{ $summary['status_counts']['matured'] }}</h2>
                                </div>
                                <div class="text-white">
                                    <i class="fas fa-clock fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Account Number</th>
                            <th>Customer Name</th>
                            <th>Agent Name</th>
                            <th>Monthly Amount</th>
                            <th>Total Deposited</th>
                            <th>Opening Date</th>
                            <th>Half Month Period</th>
                            <th>Installments Paid</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rdAccounts as $account)
                        <tr>
                            <td>{{ $account->account_number }}</td>
                            <td>{{ $account->customer->name }}</td>
                            <td>{{ $account->agent->name ?? 'N/A' }}</td>
                            <td>₹{{ number_format($account->monthly_amount, 2) }}</td>
                            <td>₹{{ number_format($account->total_deposited, 2) }}</td>
                            <td>{{ \Carbon\Carbon::parse($account->start_date)->format('d/m/Y') }}</td>
                            <td>{{ ucfirst($account->half_month_period) }} Half</td>
                            <td>{{ $account->installments_paid }}</td>
                            <td>
                                <span class="badge bg-{{ $account->status == 'active' ? 'success' : ($account->status == 'matured' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('agent.rd-agent-accounts.show', $account) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('agent.rd-agent-accounts.edit', $account) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No RD accounts found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                {{ $rdAccounts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    .card h2 {
        font-size: 1.75rem;
    }
    .btn-group {
        margin-left: 0.5rem;
    }
    .badge {
        padding: 0.5em 0.75em;
    }
    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    .table td {
        vertical-align: middle;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
    }
    .opacity-50 {
        opacity: 0.5;
    }
</style>
@endpush

<!-- RD Account Completion Modal -->
<div class="modal fade" id="completeRdAccountModal" tabindex="-1" role="dialog" aria-labelledby="completeRdAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="completeRdAccountModalLabel">Complete RD Account Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loadingSpinner" class="text-center p-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading incomplete RD accounts...</p>
                </div>
                <div id="rdAccountsList" style="display: none;">
                    <p class="text-muted mb-3">Complete the information for the following RD accounts:</p>
                    <div id="rdAccountsContainer">
                        <!-- RD accounts will be loaded here -->
                    </div>
                </div>
                <div id="noIncompleteRdAccounts" style="display: none;">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        All RD accounts are complete!
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="completeAllRdAccountsBtn" style="display: none;">Complete All</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any JavaScript functionality here
    });
    
    function showIncompleteRdAccounts() {
        const modal = new bootstrap.Modal(document.getElementById('completeRdAccountModal'));
        modal.show();
        
        // Show loading spinner
        document.getElementById('loadingSpinner').style.display = 'block';
        document.getElementById('rdAccountsList').style.display = 'none';
        document.getElementById('noIncompleteRdAccounts').style.display = 'none';
        
        // Fetch incomplete RD accounts
        fetch('{{ route("agent.rd-accounts.api.incomplete") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('loadingSpinner').style.display = 'none';
                
                if (data.count === 0) {
                    document.getElementById('noIncompleteRdAccounts').style.display = 'block';
                } else {
                    document.getElementById('rdAccountsList').style.display = 'block';
                    document.getElementById('completeAllRdAccountsBtn').style.display = 'inline-block';
                    renderRdAccounts(data.rdAccounts);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('loadingSpinner').style.display = 'none';
                document.getElementById('rdAccountsContainer').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading RD accounts. Please try again.
                    </div>
                `;
                document.getElementById('rdAccountsList').style.display = 'block';
            });
    }
    
    function renderRdAccounts(rdAccounts) {
        const container = document.getElementById('rdAccountsContainer');
        let html = '';
        
        rdAccounts.forEach(rdAccount => {
            html += `
                <div class="card mb-3 border-warning" data-rd-account-id="${rdAccount.id}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="card-title mb-2">
                                    <i class="fas fa-user me-1"></i>
                                    ${rdAccount.customer.name}
                                </h6>
                                <p class="card-text small mb-1">
                                    <strong>Account:</strong> ${rdAccount.account_number || 'Not Set'}
                                </p>
                                <p class="card-text small mb-1">
                                    <strong>Aslaas:</strong> ${rdAccount.aslaas_number || 'APPLIED'}
                                </p>
                                <p class="card-text small mb-0">
                                    <strong>Phone:</strong> ${rdAccount.registered_phone || 'Not Set'}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label small">Aslaas Number:</label>
                                    <input type="text" class="form-control form-control-sm" name="aslaas_number" value="${rdAccount.aslaas_number || ''}" required>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-6">
                                        <label class="form-label small">Phone:</label>
                                        <input type="text" class="form-control form-control-sm" name="registered_phone" value="${rdAccount.registered_phone || ''}" pattern="[0-9]{10}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Monthly Amount:</label>
                                        <input type="number" class="form-control form-control-sm" name="monthly_amount" value="${rdAccount.monthly_amount || ''}" min="100" required>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Start Date:</label>
                                    <input type="date" class="form-control form-control-sm" name="start_date" value="${rdAccount.start_date || ''}" required>
                                    <small class="text-muted">Duration will be automatically set to 60 months, Interest Rate to 6.5%, and Maturity Date will be calculated automatically.</small>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Notes:</label>
                                    <textarea class="form-control form-control-sm" name="note" rows="2" placeholder="Additional notes..." maxlength="500">${rdAccount.note || ''}</textarea>
                                </div>
                                <button type="button" class="btn btn-success btn-sm" onclick="completeRdAccount(${rdAccount.id})">
                                    <i class="fas fa-check me-1"></i> Complete This Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function completeRdAccount(rdAccountId) {
        const card = document.querySelector(`[data-rd-account-id="${rdAccountId}"]`);
        const formData = new FormData();
        
        // Get all form inputs within this card
        const inputs = card.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            formData.append(input.name, input.value);
        });
        
        // Disable the button and show loading
        const button = card.querySelector('button');
        const originalText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
        
        // Send completion request
        fetch(`{{ url('agent/rd-accounts') }}/${rdAccountId}/complete`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mark card as completed
                card.classList.remove('border-warning');
                card.classList.add('border-success');
                card.style.opacity = '0.7';
                button.innerHTML = '<i class="fas fa-check me-1"></i> Completed';
                button.disabled = true;
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-success');
                
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show mt-2';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>
                    RD Account completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                card.querySelector('.card-body').appendChild(successAlert);
                
                // Auto-remove success message after 3 seconds
                setTimeout(() => {
                    if (successAlert.parentNode) {
                        successAlert.remove();
                    }
                }, 3000);
                
                // Check if all accounts are completed
                const remainingIncomplete = document.querySelectorAll('.border-warning').length;
                if (remainingIncomplete === 0) {
                    setTimeout(() => {
                        location.reload(); // Refresh to update counts
                    }, 2000);
                }
            } else {
                throw new Error(data.message || 'Failed to complete RD account');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.innerHTML = originalText;
            
            // Show error message
            const errorAlert = document.createElement('div');
            errorAlert.className = 'alert alert-danger alert-dismissible fade show mt-2';
            errorAlert.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error: ${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            card.querySelector('.card-body').appendChild(errorAlert);
        });
    }
    
    // Complete all RD accounts button handler
    document.getElementById('completeAllRdAccountsBtn').addEventListener('click', function() {
        const incompleteCards = document.querySelectorAll('.border-warning');
        if (incompleteCards.length === 0) {
            alert('No incomplete RD accounts found.');
            return;
        }
        
        if (confirm(`Are you sure you want to complete all ${incompleteCards.length} RD accounts?`)) {
            incompleteCards.forEach(card => {
                const rdAccountId = card.getAttribute('data-rd-account-id');
                completeRdAccount(rdAccountId);
            });
        }
    });
</script>
@endpush
