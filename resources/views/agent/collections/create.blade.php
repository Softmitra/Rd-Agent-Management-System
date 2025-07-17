@extends('layouts.app')

@section('title', 'Collection Entry')

@section('content')
    <div class="container-fluid px-4">
        <h5 class="mt-1">Collection Entry</h5>
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('collections.index') }}">Collections</a></li>
            <li class="breadcrumb-item active">Collection Entry</li>
        </ol>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Customer Search Bar -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-search me-1"></i>
                Search Customer
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('collections.create') }}" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <input type="text" name="search" class="form-control" placeholder="Name, Mobile, or CIF ID"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('collections.create') }}" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>
        <!-- End Customer Search Bar -->

        @if (request('search') && isset($rdAccounts) && $rdAccounts->count() > 0)
            <!-- Search Results Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    RD Accounts - Search Results for "{{ request('search') }}"
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Account Number</th>
                                    <th>CIF ID</th>
                                    <th>Denomination</th>
                                    <th>Prem Paid</th>
                                    <th>Prem Pending</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rdAccounts as $account)
                                    @php
                                        $startDate = \Carbon\Carbon::parse($account->start_date);
                                        $today = \Carbon\Carbon::now();
                                        $totalMonths = $startDate->diffInMonths($today) + 1;
                                        $pendingMonths = $totalMonths - $account->installments_paid;
                                        $pendingMonths = max(0, $pendingMonths); // Ensure non-negative
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $account->customer->name }}</strong></td>
                                        <td>{{ $account->account_number }}</td>
                                        <td>{{ $account->customer->cif_id ?? 'N/A' }}</td>
                                        <td>₹{{ number_format($account->monthly_amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $account->installments_paid }}</span>
                                        </td>
                                        <td>
                                            @if ($pendingMonths > 0)
                                                <span class="badge bg-warning">{{ $pendingMonths }}</span>
                                            @else
                                                <span class="badge bg-success">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($pendingMonths >= 6)
                                                <span class="badge bg-danger">Due ({{ $pendingMonths }}+ months)</span>
                                            @elseif($pendingMonths > 0)
                                                <span class="badge bg-warning">{{ $pendingMonths }} months due</span>
                                            @else
                                                <span class="badge bg-success">Up to date</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('collections.show', $account->id) }}"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            <tfoot>
                                <tr class="table-active">
                                    <td colspan="1" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2" class="text-end">
                                        <strong>Row : <strong>{{ $rdAccounts->count() }}</strong></strong>
                                    </td>
                                    <td><strong>₹{{ number_format($rdAccounts->sum('monthly_amount'), 2) }}</strong></td>
                                    <td><strong>{{ $rdAccounts->sum('installments_paid') }}</strong></td>
                                    <td><strong>{{ $rdAccounts->sum(function ($account) {
                                        $startDate = \Carbon\Carbon::parse($account->start_date);
                                        $today = \Carbon\Carbon::now();
                                        $totalMonths = $startDate->diffInMonths($today) + 1;
                                        return max(0, $totalMonths - $account->installments_paid);
                                    }) }}</strong>
                                    </td>
                                    <td colspan="2"></td>
                                </tr>

                            </tfoot>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary">Collect Amount</button>
                    </div>
                </div>
            </div>
            <!-- End Search Results Table -->
        @elseif(request('search'))
            <!-- No Results Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>No RD Accounts Found</h5>
                    <p class="text-muted">No active RD accounts found for "{{ request('search') }}". Please try a different
                        search term.</p>
                </div>
            </div>
        @else
            <!-- Default Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>Search for Customer</h5>
                    <p class="text-muted">Enter customer name, mobile number, or CIF ID to view their active RD accounts and
                        enter collections.</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Collection Entry Modal -->
    <div class="modal fade" id="collectionModal" tabindex="-1" aria-labelledby="collectionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="collectionModalLabel">Enter Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('collections.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="modal_rd_account_id" name="rd_account_id">

                        <!-- Account Information -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Account Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Customer Name:</strong> <span id="modal_customer_name"></span></p>
                                        <p><strong>Account Number:</strong> <span id="modal_account_number"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Monthly Amount:</strong> ₹<span id="modal_monthly_amount"></span></p>
                                        <p><strong>Pending Months:</strong> <span id="modal_pending_months"></span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_date" class="form-label">Payment Date</label>
                                    <input type="date" class="form-control" id="payment_date" name="payment_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="payment_mode" class="form-label">Payment Type</label>
                                    <select class="form-select" id="payment_mode" name="payment_mode" required>
                                        <option value="">Select Payment Type</option>
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="note" class="form-label">Note</label>
                                    <input type="text" class="form-control" id="note" name="note">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01"
                                required readonly>
                        </div>

                        <!-- Payment Mode Specific Fields -->
                        <div class="row" id="cheque_details" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cheque_number" class="form-label">Cheque Number</label>
                                    <input type="text" class="form-control" id="cheque_number" name="cheque_number">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name">
                                </div>
                            </div>
                        </div>

                        <div class="row" id="online_details" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="transaction_id" class="form-label">Transaction ID</label>
                                    <input type="text" class="form-control" id="transaction_id"
                                        name="transaction_id">
                                </div>
                            </div>
                        </div>

                        <div class="row" id="upi_details" style="display: none;">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="upi_id" class="form-label">UPI ID</label>
                                    <input type="text" class="form-control" id="upi_id" name="upi_id">
                                </div>
                            </div>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="card" id="amount_breakdown" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0">Amount Breakdown</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Base Amount:</strong> ₹<span id="base_amount">0.00</span></p>
                                        <p><strong>Penalty:</strong> ₹<span id="penalty_amount">0.00</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Rebate:</strong> ₹<span id="rebate_amount">0.00</span></p>
                                        <p><strong>Total Amount:</strong> ₹<span id="total_amount">0.00</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Collection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentMonthlyAmount = 0;
        let currentPendingMonths = 0;

        function openCollectionModal(rdAccountId, customerName, accountNumber, monthlyAmount, pendingMonths) {
            currentMonthlyAmount = monthlyAmount;
            currentPendingMonths = pendingMonths;

            // Populate modal with account details
            document.getElementById('modal_rd_account_id').value = rdAccountId;
            document.getElementById('modal_customer_name').textContent = customerName;
            document.getElementById('modal_account_number').textContent = accountNumber;
            document.getElementById('modal_monthly_amount').textContent = monthlyAmount.toFixed(2);
            document.getElementById('modal_pending_months').textContent = pendingMonths;

            // Populate months dropdown
            const monthsSelect = document.getElementById('pending_months');
            monthsSelect.innerHTML = '<option value="">Select Months</option>';

            for (let i = 1; i <= Math.min(12, pendingMonths); i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `${i} Month${i > 1 ? 's' : ''}`;
                monthsSelect.appendChild(option);
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('collectionModal'));
            modal.show();
        }

        document.getElementById('pending_months').addEventListener('change', function() {
            const selectedMonths = parseInt(this.value);
            const amountBreakdown = document.getElementById('amount_breakdown');

            if (selectedMonths > 0) {
                amountBreakdown.style.display = 'block';

                // Calculate base amount
                const baseAmount = currentMonthlyAmount * selectedMonths;
                document.getElementById('base_amount').textContent = baseAmount.toFixed(2);

                // Calculate penalty (simplified - ₹5 per month for overdue)
                let penalty = 0;
                if (currentPendingMonths > 0) {
                    const monthsForPenalty = Math.min(currentPendingMonths, selectedMonths);
                    for (let i = 1; i <= monthsForPenalty; i++) {
                        penalty += (i * 5);
                    }
                }
                document.getElementById('penalty_amount').textContent = penalty.toFixed(2);

                // Calculate rebate
                let rebate = 0;
                if (selectedMonths === 6) {
                    rebate = (currentMonthlyAmount / 100) * 10;
                } else if (selectedMonths === 12) {
                    rebate = (currentMonthlyAmount / 100) * 40;
                }
                document.getElementById('rebate_amount').textContent = rebate.toFixed(2);

                // Calculate total
                const totalAmount = baseAmount + penalty - rebate;
                document.getElementById('total_amount').textContent = totalAmount.toFixed(2);
                document.getElementById('amount').value = totalAmount.toFixed(2);
            } else {
                amountBreakdown.style.display = 'none';
                document.getElementById('amount').value = '';
            }
        });

        document.getElementById('payment_mode').addEventListener('change', function() {
            const chequeDetails = document.getElementById('cheque_details');
            const onlineDetails = document.getElementById('online_details');
            const upiDetails = document.getElementById('upi_details');

            chequeDetails.style.display = 'none';
            onlineDetails.style.display = 'none';
            upiDetails.style.display = 'none';

            if (this.value === 'cheque') {
                chequeDetails.style.display = 'flex';
            } else if (this.value === 'online') {
                onlineDetails.style.display = 'flex';
            } else if (this.value === 'upi') {
                upiDetails.style.display = 'flex';
            }
        });
    </script>
@endpush
