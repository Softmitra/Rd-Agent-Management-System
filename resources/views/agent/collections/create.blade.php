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
                                    <th>Prem. Paid</th>
                                    <th>Prem. Pending</th>
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
                        <button type="button" class="btn btn-primary" data-toggle="modal"
                            data-target="#collectionModal">Collect Amount</button>

                        <!-- Collection Modal -->
                        <div class="modal fade" id="collectionModal" tabindex="-1" aria-labelledby="collectionModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="collectionModalLabel">Enter Collection Details</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form action="{{ route('collections.store') }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="mb-3 col-12">
                                                    <label for="collectionDate" class="form-label">Date</label>
                                                    <input type="date" class="form-control" id="collectionDate"
                                                        name="date" value="{{ date('Y-m-d') }}" required>
                                                </div>
                                                <div class="mb-3 col-12">
                                                    <label for="paymentType" class="form-label d-block">Payment Type</label>
                                                    <select class="form-control w-100 select2" id="paymentType"
                                                        name="payment_type" required>
                                                        <option value="cash">Cash</option>
                                                        <option value="upi">UPI</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3 col-12">
                                                    <label for="amount" class="form-label">Amount</label>
                                                    <input type="number" class="form-control" id="amount" name="amount"
                                                        step="0.01" required>
                                                </div>
                                                <div class="mb-3 col-12">
                                                    <label for="note" class="form-label">Note</label>
                                                    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Submit Collection</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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
                    <p class="text-muted">No active RD accounts found for "{{ request('search') }}". Please try a
                        different
                        search term.</p>
                </div>
            </div>
        @else
            <!-- Default Message -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>Search for Customer</h5>
                    <p class="text-muted">Enter customer name, mobile number, or CIF ID to view their active RD accounts
                        and
                        enter collections.</p>
                </div>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Set default date
            $('#collectionDate').val(new Date().toISOString().substr(0, 10));

            // Initialize modal with jQuery
            $('#collectionModal').modal({
                show: false,
                backdrop: 'static'
            });

            // Debug modal show
            $('[data-bs-target="#collectionModal"]').click(function() {
                console.log('Button clicked, showing modal');
                $('#collectionModal').modal('show');
            });
        });
    </script>
@endpush
