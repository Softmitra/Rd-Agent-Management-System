@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mt-4 mb-2">
                <i class="fas fa-users text-primary me-2"></i>My Customers
            </h1>
            <div class="d-flex gap-3 mb-2">
                <div class="badge bg-primary fs-6 p-2">
                    <i class="fas fa-users me-1"></i>
                    Total: {{ $totalCustomers ?? 0 }}
                </div>
                <div class="badge bg-success fs-6 p-2">
                    <i class="fas fa-check-circle me-1"></i>
                    Complete: {{ $completeCustomers ?? 0 }}
                </div>
                <div class="badge bg-warning fs-6 p-2">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Incomplete: {{ $incompleteCustomers ?? 0 }}
                </div>
            </div>
        </div>
        <div>
            @if($incompleteCustomers > 0)
                <button type="button" class="btn btn-warning me-2" onclick="showIncompleteCustomers()">
                    <i class="fas fa-exclamation-triangle"></i>
                    Complete {{ $incompleteCustomers }} Records
                </button>
            @endif
            <a href="{{ route('agent.customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Customer
            </a>
        </div>
    </div>

    @if($incompleteCustomers > 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Action Required:</strong> You have {{ $incompleteCustomers }} customers with incomplete details. 
            Please complete their information to ensure proper record keeping.
            <button type="button" class="btn btn-sm btn-outline-warning ms-2" onclick="showIncompleteCustomers()">
                Complete Now
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Customer List
            </h5>
        </div>
        <div class="card-body">
            @if($customers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>RD Accounts</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $customer->name }}</strong>
                                                @if($customer->email)
                                                    <br><small class="text-muted">{{ $customer->email }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($customer->mobile_number && !str_starts_with($customer->mobile_number, '999'))
                                            <span class="badge bg-success">{{ $customer->mobile_number }}</span>
                                        @else
                                            <span class="badge bg-warning">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>{{ $customer->rdAccounts->count() }}</td>
                                    <td>₹{{ number_format($customer->rdAccounts->sum('monthly_amount'), 2) }}</td>
                                    <td>
                                        @if($customer->isInfoComplete())
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Complete</span>
                                        @else
                                            <span class="badge bg-warning"><i class="fas fa-exclamation-triangle"></i> Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.customers.show', $customer) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('agent.customers.edit', $customer) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No Customers Found</h5>
                    <p class="text-muted">You haven't been assigned any customers yet.</p>
                    <a href="{{ route('agent.customers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Your First Customer
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Incomplete Records Modal -->
<div class="modal fade" id="incompleteRecordsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                    Complete Customer Records
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    Please complete the missing information for your customers to ensure proper record keeping.
                </p>
                <div id="incompleteCustomersList">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function showIncompleteCustomers() {
    const modal = new bootstrap.Modal(document.getElementById('incompleteRecordsModal'));
    modal.show();
    
    try {
        const response = await fetch('{{ route("agent.customers.api.incomplete") }}');
        const data = await response.json();
        
        const container = document.getElementById('incompleteCustomersList');
        
        if (data.customers.length === 0) {
            container.innerHTML = `
                <div class="text-center py-3">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All Records Complete!</h5>
                    <p class="text-muted">All your customer records have complete information.</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        data.customers.forEach(customer => {
            html += `
                <div class="card mb-3" id="customer-card-${customer.id}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>${customer.name}</h6>
                                <p class="text-muted mb-1">
                                    RD Accounts: ${customer.rd_accounts.length}<br>
                                    Current Phone: ${customer.mobile_number || 'Not set'}
                                </p>
                            </div>
                            <div class="col-md-6">
                                <form onsubmit="completeCustomer(event, ${customer.id})">
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <input type="tel" class="form-control form-control-sm" 
                                                       name="mobile_number" placeholder="Mobile Number *" 
                                                       value="${customer.mobile_number && !customer.mobile_number.startsWith('999') ? customer.mobile_number : ''}"
                                                       pattern="[0-9]{10}" maxlength="10" required>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <input type="tel" class="form-control form-control-sm" 
                                                       name="phone" placeholder="Alternate Phone"
                                                       value="${customer.phone && !customer.phone.startsWith('999') ? customer.phone : ''}"
                                                       pattern="[0-9]{10}" maxlength="10">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <textarea class="form-control form-control-sm" 
                                                  name="address" placeholder="Address" rows="2">${customer.address || ''}</textarea>
                                    </div>
                                    <div class="mb-2">
                                        <input type="email" class="form-control form-control-sm" 
                                               name="email" placeholder="Email (Optional)"
                                               value="${customer.email || ''}">
                                    </div>
                                    <div class="mb-2">
                                        <input type="text" class="form-control form-control-sm" 
                                               name="cif_id" placeholder="CIF ID *" maxlength="10"
                                               value="${customer.cif_id || ''}" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="aadhar_number" placeholder="Aadhar Number"
                                                       value="${customer.aadhar_number || ''}"
                                                       pattern="[0-9]{12}" maxlength="12">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <input type="text" class="form-control form-control-sm" 
                                                       name="pan_number" placeholder="PAN Number"
                                                       value="${customer.pan_number || ''}"
                                                       pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10" 
                                                       style="text-transform: uppercase;">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <input type="date" class="form-control form-control-sm" 
                                                       name="date_of_birth" placeholder="Date of Birth"
                                                       value="${customer.date_of_birth || ''}">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-2">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" 
                                                           name="has_savings_account" value="1" 
                                                           id="savings_${customer.id}" 
                                                           ${customer.has_savings_account ? 'checked' : ''}>
                                                    <label class="form-check-label small" for="savings_${customer.id}">
                                                        Has Savings A/c
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-2" id="savings_section_${customer.id}" 
                                         style="display: ${customer.has_savings_account ? 'block' : 'none'}">
                                        <input type="text" class="form-control form-control-sm" 
                                               name="savings_account_no" placeholder="Savings Account Number"
                                               value="${customer.savings_account_no || ''}">
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Add event listeners for dynamic functionality
        data.customers.forEach(customer => {
            // Savings account checkbox toggle
            const savingsCheckbox = document.getElementById(`savings_${customer.id}`);
            const savingsSection = document.getElementById(`savings_section_${customer.id}`);
            
            if (savingsCheckbox && savingsSection) {
                savingsCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        savingsSection.style.display = 'block';
                    } else {
                        savingsSection.style.display = 'none';
                        savingsSection.querySelector('input').value = '';
                    }
                });
            }
            
            // PAN number uppercase formatting
            const panInput = document.querySelector(`#customer-card-${customer.id} input[name="pan_number"]`);
            if (panInput) {
                panInput.addEventListener('input', function(e) {
                    e.target.value = e.target.value.toUpperCase();
                });
            }
        });
        
    } catch (error) {
        console.error('Error loading incomplete customers:', error);
        document.getElementById('incompleteCustomersList').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                Error loading customer data. Please try again.
            </div>
        `;
    }
}

async function completeCustomer(event, customerId) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`/agent/customers/${customerId}/complete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Remove the completed customer card
            document.getElementById(`customer-card-${customerId}`).remove();
            
            // Show success message
            showAlert('Customer details completed successfully!', 'success');
            
            // If all customers are completed, update the modal
            const remainingCards = document.querySelectorAll('[id^="customer-card-"]');
            if (remainingCards.length === 0) {
                document.getElementById('incompleteCustomersList').innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>All Records Complete!</h5>
                        <p class="text-muted">All customer records have been completed.</p>
                    </div>
                `;
                
                // Reload page after 2 seconds to update the counts
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
            
        } else {
            showAlert('Error completing customer details. Please try again.', 'danger');
        }
        
    } catch (error) {
        console.error('Error completing customer:', error);
        showAlert('Error completing customer details. Please try again.', 'danger');
    }
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('.container-fluid').insertBefore(alert, document.querySelector('.container-fluid').firstChild);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}
</script>
@endpush
@endsection
