@extends('layouts.app')

@section('title', 'Collections')

@section('content')
    <div class="container-fluid px-4">
        <h5 class="mt-1">Collections</h5>
        <ol class="breadcrumb mb-3">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Collections</li>
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

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">Collection Records</h6>
            <a href="{{ route('collections.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Collection Entry
            </a>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-filter me-1"></i>
                Filters
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('collections.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="account_number" class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" placeholder="Search account..."
                            value="{{ request('account_number') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" placeholder="Search customer..."
                            value="{{ request('customer_name') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date" class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control"
                            value="{{ request('payment_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Not in Lot" {{ request('status') == 'Not in Lot' ? 'selected' : '' }}>Not in Lot
                            </option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('collections.index') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Collections Table -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-table me-1"></i>
                Collection Records
            </div>
            <div class="card-body">
                @if ($collections->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Receipt No.</th>
                                    <th>Customer Name</th>
                                    <th>Account Number</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Payment Mode</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($collections as $collection)
                                    <tr>
                                        <td><strong>{{ $collection->receipt_number }}</strong></td>
                                        <td>{{ $collection->rdAccount->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $collection->rdAccount->account_number ?? 'N/A' }}</td>
                                        <td>₹{{ number_format($collection->amount, 2) }}</td>
                                        <td>{{ $collection->date ? \Carbon\Carbon::parse($collection->date)->format('d M Y') : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ ucfirst($collection->payment_method) }}</span>
                                        </td>
                                        <td>
                                            @if ($collection->status == 'Not in Lot')
                                                <span class="badge bg-warning">{{ $collection->status }}</span>
                                            @elseif($collection->status == 'completed')
                                                <span class="badge bg-success">{{ ucfirst($collection->status) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($collection->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $collection->remarks ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('payments.show', $collection->id) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($collection->status == 'Not in Lot')
                                                    <button class="btn btn-outline-success btn-sm"
                                                        onclick="markInLot({{ $collection->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $collections->appends(request()->query())->links() }}
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5>No Collections Found</h5>
                        <p class="text-muted">No collection records found matching your criteria.</p>
                        <a href="{{ route('collections.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add First Collection
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function markInLot(collectionId) {
            if (confirm('Mark this collection as "In Lot"?')) {
                // Add AJAX call to update collection status
                fetch(`/collections/${collectionId}/mark-in-lot`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error updating status');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating status');
                    });
            }
        }
    </script>
@endpush
