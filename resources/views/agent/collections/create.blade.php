@extends('layouts.app')

@section('title', 'Collection Entry')

@push('styles')
<!-- Bootstrap Datepicker CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />

<style>
.step-indicator {
    display: flex;
    justify-content: center;
    margin-bottom: 2rem;
}

.step {
    display: flex;
    align-items: center;
    padding: 0.5rem 1rem;
    margin: 0 0.5rem;
    border-radius: 25px;
    transition: all 0.3s ease;
}

.step.active {
    background-color: #007bff;
    color: white;
}

.step.completed {
    background-color: #28a745;
    color: white;
}

.step.inactive {
    background-color: #e9ecef;
    color: #6c757d;
}

.step-number {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 0.5rem;
    font-size: 0.875rem;
    font-weight: bold;
}

.customer-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.customer-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.customer-card.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.account-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.account-card:hover {
    border-color: #007bff;
    box-shadow: 0 0.5rem 1rem rgba(0, 123, 255, 0.15);
    transform: translateY(-2px);
}

.account-card.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.collection-form {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 2rem;
}

.form-floating .form-control:focus ~ label,
.form-floating .form-control:not(:placeholder-shown) ~ label {
    color: #007bff;
}

.btn-step {
    min-width: 120px;
}

.bg-gradient-success {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
}

.form-control.border-2 {
    border-width: 2px !important;
}

.form-control.border-2:focus {
    border-color: #007bff !important;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25) !important;
}

.card.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
}

.collection-form .form-floating .form-control {
    border-radius: 0.5rem;
}

.collection-form .form-floating label {
    font-weight: 500;
}

.collection-form .btn {
    border-radius: 0.5rem;
    font-weight: 500;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Collection Entry</h5>
        <div>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('collections.index') }}">Collections</a></li>
                <li class="breadcrumb-item active">New Entry</li>
            </ol>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step active" id="step-1">
                    <div class="step-number">1</div>
                    <span>Search Customer</span>
                </div>
                <div class="step inactive" id="step-2">
                    <div class="step-number">2</div>
                    <span>Collection Entry</span>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="fas fa-times me-2"></i>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Step 1: Customer Search -->
            <div class="card mb-4" id="customer-search-section">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-search me-2"></i>Step 1: Search Customer
                    </h6>
                </div>
                <div class="card-body">
                    <form id="customer-search-form">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="search-name" 
                                           name="search_name" 
                                           placeholder="Customer Name">
                                    <label for="search-name">Customer Name</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" 
                                           class="form-control" 
                                           id="search-mobile" 
                                           name="search_mobile" 
                                           placeholder="Mobile Number"
                                           pattern="[0-9]{10}">
                                    <label for="search-mobile">Mobile Number</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="text" maxlength="10"
                                           class="form-control" 
                                           id="search-cif" 
                                           name="search_cif" 
                                           placeholder="CIF ID">
                                    <label for="search-cif">CIF ID</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary btn-step">
                                <i class="fas fa-search me-2"></i>Search Customer
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-step ms-2" onclick="clearCustomerSearch()">
                                <i class="fas fa-times me-2"></i>Clear
                            </button>
                            <button type="button" class="btn btn-outline-info btn-step ms-2" onclick="debugShowAllCustomers()">
                                <i class="fas fa-bug me-2"></i>Debug: Show All
                            </button>
                        </div>
                    </form>

                    <!-- Customer Results -->
                    <div id="customer-results" style="display: none;">
                        <hr class="my-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-users me-2"></i>Search Results
                        </h6>
                        <div id="customer-list" class="row">
                            <!-- Dynamic customer cards will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer's RD Accounts Table -->
            <div class="card mb-4" id="accounts-table-section" style="display: none;">
                <div class="card-header bg-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-piggy-bank me-2"></i>RD Accounts - Collection Status
                        </h6>
                        <div>
                            <span id="selected-customer-info" class="text-white"></span>
                            <button type="button" class="btn btn-sm btn-outline-light ms-2" onclick="goBackToCustomerSearch()">
                                <i class="fas fa-arrow-left me-1"></i>Change Customer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Summary Statistics -->
                    <div id="accounts-summary" class="mb-3">
                        <!-- Dynamic summary will be loaded here -->
                    </div>
                    
                    <!-- Accounts Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="accounts-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Account No.</th>
                                    <th>Monthly Deposit</th>
                                    <th>Paid Months</th>
                                    <th>Months Defaulted</th>
                                    <th>Total Penalty</th>
                                    <th>Total Payable</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="accounts-table-body">
                                <!-- Dynamic account rows will be loaded here -->
                            </tbody>
                            <tfoot>
                                <tr id="total-row" class="table-success fw-bold" style="display: none;">
                                    <td colspan="1">TOTAL</td>
                                    <td id="total-monthly">₹0</td>
                                    <td class="text-center">-</td>
                                    <td class="text-center">-</td>
                                    <td id="total-penalty">₹0</td>
                                    <td id="total-payable">₹0</td>
                                    <td>-</td>
                                </tr>
                                <tr id="collection-row" style="display: none;">
                                    <td colspan="8" class="text-center bg-light">
                                        <button type="button" class="btn btn-success btn-lg" onclick="openCollectionForm()">
                                            <i class="fas fa-hand-holding-usd me-2"></i>
                                            Collect Total Amount: <span id="collection-amount-btn">₹0</span>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Step 2: Combined Collection Entry Form -->
            <div class="card shadow-lg border-0" id="collection-form-section" style="display: none;">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-hand-holding-usd me-2"></i>Collection Entry
                        </h5>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="closeCollectionForm()">
                            <i class="fas fa-times me-1"></i>Close
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Collection Summary Section -->
                    <div class="bg-light border-bottom p-4">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Customer</small>
                                                <strong id="collection-customer-name" class="text-dark">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-piggy-bank text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Accounts</small>
                                                <strong id="collection-total-accounts" class="text-dark">-</strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                <i class="fas fa-exclamation-triangle text-white"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Total Pending</small>
                                                <strong id="collection-pending-amount" class="text-danger fs-5">₹0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="alert alert-primary mb-0 h-100 d-flex align-items-center">
                                    <div>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Combined Collection</strong>
                                        <br><small>This will create one collection entry for all selected accounts</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collection Form -->
                    <div class="p-4">
                        <form action="{{ route('collections.store') }}" method="POST" id="collection-entry-form">
                            @csrf
                            <input type="hidden" id="selected-customer-id" name="customer_id">

                            <!-- Basic Collection Details -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-calendar-alt me-2"></i>Collection Details
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="date" 
                                               class="form-control border-2" 
                                               id="collection-date" 
                                               name="date" 
                                               required 
                                               max="{{ date('Y-m-d') }}"
                                               value="{{ date('Y-m-d') }}">
                                        <label for="collection-date">Collection Date *</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select border-2" id="payment-type" name="payment_type" required>
                                            <option value="">Select Payment Mode</option>
                                            <option value="cash">💵 Cash</option>
                                            <option value="upi">📱 UPI</option>
                                            <option value="cheque">📄 Cheque</option>
                                            <option value="online">💻 Online Transfer</option>
                                        </select>
                                        <label for="payment-type">Payment Mode *</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Amount Section -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-rupee-sign me-2"></i>Amount Details
                                    </h6>
                                </div>
                                <div class="col-lg-8">
                                    <div class="form-floating">
                                        <input type="number" 
                                               class="form-control border-2 fs-4" 
                                               id="amount" 
                                               name="amount" 
                                               min="1" 
                                               step="0.01" 
                                               required 
                                               placeholder="Amount"
                                               style="height: 70px;">
                                        <label for="amount" class="fs-5">Collection Amount (₹) *</label>
                                    </div>
                                    <div class="mt-2">
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-danger rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                                                        <i class="fas fa-exclamation text-white" style="font-size: 10px;"></i>
                                                    </div>
                                                    <small>Pending: <strong id="amount-helper-pending" class="text-danger">₹0</strong></small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 20px; height: 20px;">
                                                        <i class="fas fa-calendar text-white" style="font-size: 10px;"></i>
                                                    </div>
                                                    <small>Monthly: <strong id="amount-helper-monthly" class="text-primary">₹0</strong></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-danger" onclick="setAmountToPending()">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Set Pending Amount
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" onclick="setAmountToMonthly()">
                                            <i class="fas fa-calendar me-2"></i>Set Monthly Total
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Details -->
                            <div class="row g-4 mb-4">
                                <div class="col-12">
                                    <h6 class="text-muted mb-3">
                                        <i class="fas fa-file-alt me-2"></i>Additional Information (Optional)
                                    </h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="receipt-number" 
                                                   name="receipt_number" 
                                                   placeholder="Receipt Number"
                                                   readonly>
                                            <button class="btn btn-outline-primary" type="button" id="generate-receipt-btn" onclick="generateReceiptNumber()">
                                                <i class="fas fa-sync-alt me-1"></i>Generate
                                            </button>
                                        </div>
                                        <label for="receipt-number">Receipt Number (Auto-generated)</label>
                                    </div>
                                    <small class="text-muted mt-1">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Format: [First3+Last1]-YYMMDD-HHMMSS (e.g., 9871-250815-143052)
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" 
                                               class="form-control" 
                                               id="transaction-ref" 
                                               name="transaction_ref" 
                                               placeholder="Transaction Reference">
                                        <label for="transaction-ref">Transaction Reference</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" 
                                                  id="note" 
                                                  name="note" 
                                                  placeholder="Collection Notes"
                                                  style="height: 120px;"></textarea>
                                        <label for="note">Collection Notes</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Section -->
                            <div class="border-top pt-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-success border-0">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <div>
                                                    <strong>Ready to Submit</strong><br>
                                                    <small>This will create a combined collection entry for all accounts</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-flex align-items-center justify-content-end">
                                        <button type="submit" class="btn btn-success btn-lg px-5 me-3">
                                            <i class="fas fa-save me-2"></i>Submit Collection
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" onclick="closeCollectionForm()">
                                            <i class="fas fa-times me-2"></i>Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
let selectedCustomer = null;
let selectedAccount = null;

$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Focus on first input
    $('#search-name').focus();
});

// Customer Search Form Submit
$('#customer-search-form').on('submit', function(e) {
    e.preventDefault();
    searchCustomers();
});

function searchCustomers() {
    const searchName = $('#search-name').val().trim();
    const searchMobile = $('#search-mobile').val().trim();
    const searchCif = $('#search-cif').val().trim();
    
    console.log('Search initiated:', {
        searchName: searchName,
        searchMobile: searchMobile,
        searchCif: searchCif
    });
    
    if (!searchName && !searchMobile && !searchCif) {
        alert('Please enter at least one search criteria');
        return;
    }
    
    // Show loading
    $('#customer-list').html(`
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Searching...</span>
            </div>
            <p class="mt-2 text-muted">Searching customers...</p>
        </div>
    `);
    $('#customer-results').show();
    
    // Make AJAX request
    $.ajax({
        url: '{{ route("collections.create") }}',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            search_name: searchName,
            search_mobile: searchMobile,
            search_cif: searchCif
        },
        success: function(response) {
            console.log('AJAX Success Response:', response);
            if (response.success) {
                displayCustomers(response.customers || []);
            } else {
                $('#customer-list').html(`
                    <div class="col-12 text-center">
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            ${response.message || 'No customers found'}
                        </div>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            
            $('#customer-list').html(`
                <div class="col-12 text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error searching customers: ${error}<br>
                        <small>Check console for details</small>
                    </div>
                </div>
            `);
        }
    });
}

function displayCustomers(customers) {
    if (customers.length === 0) {
        $('#customer-list').html(`
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No customers found matching your search criteria.
                </div>
            </div>
        `);
        return;
    }
    
    let html = '';
    customers.forEach(customer => {
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card customer-card h-100" onclick="selectCustomer(${customer.id}, '${customer.name}', '${customer.mobile_number || ''}', '${customer.cif_id || ''}')">
                    <div class="card-body">
                        <h6 class="card-title text-primary">
                            <i class="fas fa-user me-2"></i>${customer.name}
                        </h6>
                        <p class="card-text mb-1">
                            <small class="text-muted">
                                <i class="fas fa-phone me-1"></i>${customer.mobile_number || 'N/A'}
                            </small>
                        </p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-id-card me-1"></i>CIF: ${customer.cif_id || 'N/A'}
                            </small>
                        </p>
                        <div class="text-end">
                            <span class="badge bg-primary">Select</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    $('#customer-list').html(html);
}

function selectCustomer(customerId, customerName, mobile, cifId) {
    selectedCustomer = {
        id: customerId,
        name: customerName,
        mobile: mobile,
        cif_id: cifId
    };
    
    // Update step indicator - skip to step 2 since we're showing accounts directly
    updateStepIndicator(1); // Keep step 1 active until accounts are loaded
    
    // Show customer info
    $('#selected-customer-info').html(`<strong>${customerName}</strong> (${mobile || 'No mobile'})`);
    
    // Load accounts for this customer
    loadCustomerAccounts(customerId);
    
    // Show accounts table section
    $('#accounts-table-section').show();
    
    // Scroll to accounts table
    $('html, body').animate({
        scrollTop: $('#accounts-table-section').offset().top - 100
    }, 500);
}

function loadCustomerAccounts(customerId) {
    console.log('Loading accounts for customer ID:', customerId);
    
    $('#accounts-table-body').html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Loading accounts...</span>
                </div>
                <p class="mt-2 text-muted">Loading RD accounts...</p>
            </td>
        </tr>
    `);
    $('#accounts-summary').html('');
    
    // Make AJAX request to get customer's RD accounts
    $.ajax({
        url: '{{ route("collections.create") }}',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            customer_id: customerId
        },
        success: function(response) {
            console.log('=== ACCOUNTS AJAX SUCCESS ===');
            console.log('Full Response:', response);
            console.log('Response type:', typeof response);
            console.log('Response Success:', response.success);
            console.log('RD Accounts exists:', 'rd_accounts' in response);
            console.log('RD Accounts type:', typeof response.rd_accounts);
            console.log('RD Accounts Array:', response.rd_accounts);
            console.log('RD Accounts Length:', response.rd_accounts ? response.rd_accounts.length : 'undefined or null');
            
            try {
                if (response.success && response.rd_accounts) {
                    console.log('=== CALLING displayAccounts ===');
                    console.log('About to call displayAccounts with:', response.rd_accounts);
                    displayAccounts(response.rd_accounts);
                    console.log('=== displayAccounts call completed ===');
                } else {
                    console.log('=== NO ACCOUNTS CONDITION ===');
                    console.log('response.success:', response.success);
                    console.log('response.rd_accounts:', response.rd_accounts);
                    $('#accounts-table-body').html(`
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    ${response.message || 'No accounts found'}
                                </div>
                            </td>
                        </tr>
                    `);
                    $('#accounts-summary').html('');
                    $('#total-row, #collection-row').hide();
                }
            } catch (error) {
                console.error('=== ERROR IN ACCOUNTS SUCCESS HANDLER ===');
                console.error('Error:', error);
                console.error('Stack:', error.stack);
                $('#accounts-table-body').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                JavaScript Error: ${error.message}
                            </div>
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading accounts:', {
                xhr: xhr,
                status: status,
                error: error,
                responseText: xhr.responseText
            });
            
            $('#accounts-table-body').html(`
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error loading accounts: ${error}<br>
                            <small>Check console for details</small>
                        </div>
                    </td>
                </tr>
            `);
            $('#accounts-summary').html('');
        }
    });
}

function displayAccounts(accounts) {
    console.log('displayAccounts called with:', accounts);
    console.log('Type of accounts:', typeof accounts);
    console.log('Is accounts an array:', Array.isArray(accounts));
    console.log('Number of accounts:', accounts ? accounts.length : 'accounts is null/undefined');
    console.log('First account details:', accounts && accounts.length > 0 ? accounts[0] : 'No accounts');
    
    // Check if accounts is null or undefined
    if (!accounts) {
        console.error('Accounts is null or undefined');
        $('#accounts-table-body').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error: Account data is null or undefined.
                    </div>
                </td>
            </tr>
        `);
        return;
    }
    
    // Filter out null/undefined accounts
    accounts = accounts.filter(account => account);
    
    if (!accounts || accounts.length === 0) {
        console.log('No accounts to display');
        $('#accounts-table-body').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No RD accounts found for this customer.
                    </div>
                </td>
            </tr>
        `);
        $('#accounts-summary').html('');
        $('#total-row, #collection-row').hide();
        return;
    }
    
    // Sort accounts by account number
    accounts.sort((a, b) => a.account_number.localeCompare(b.account_number));
    
    // Calculate totals
    let totalMonthly = 0;
    let totalPenalty = 0;
    let totalPayable = 0;
    let discontinuedAccounts = 0;
    
    // Generate summary
    const totalAccounts = accounts.length;
    const activeAccounts = accounts.filter(a => a.status === 'active' && !a.is_discontinued).length;
    const inactiveAccounts = totalAccounts - activeAccounts;
    
    const summaryHtml = `
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body py-2">
                        <h6 class="mb-1">${totalAccounts}</h6>
                        <small>Total Accounts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body py-2">
                        <h6 class="mb-1">${activeAccounts}</h6>
                        <small>Active Accounts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-secondary text-white">
                    <div class="card-body py-2">
                        <h6 class="mb-1">${inactiveAccounts}</h6>
                        <small>Inactive Accounts</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body py-2">
                        <h6 class="mb-1" id="summary-pending">₹0</h6>
                        <small>Total Pending</small>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#accounts-summary').html(summaryHtml);
    
    // Generate table rows
    let tableHtml = '';
    accounts.forEach(account => {
        // New penalty-related values
        const missedMonths = parseInt(account.missed_months || 0);
        const penaltyPerMonth = parseFloat(account.penalty_per_month || 0);
        const accountPenalty = parseFloat(account.total_penalty || 0);
        const accountPayable = parseFloat(account.total_payable || 0);
        const isDiscontinued = account.is_discontinued || false;
        const computedStatus = account.computed_status || account.status;
        
        // Existing values
        const statusColor = computedStatus === 'discontinued' ? 'danger' : (account.status === 'active' ? 'success' : 'secondary');
        const monthlyAmount = parseFloat(account.monthly_amount || 0);
        const installmentsPaid = parseInt(account.installments_paid || 0);
        
        // Determine row class based on missed months
        const rowClass = isDiscontinued ? 'table-danger' : 
                        missedMonths >= 3 ? 'table-danger' :
                        missedMonths >= 1 ? 'table-warning' : '';
        
        // Add to totals with penalty
        totalMonthly += monthlyAmount;
        totalPenalty += accountPenalty;
        totalPayable += accountPayable;
        
        if (isDiscontinued) {
            discontinuedAccounts++;
        }
        
        tableHtml += `
            <tr class="${rowClass}">
                <td>
                    <strong>${account.account_number}</strong>
                    <br><small class="text-muted">Started: ${new Date(account.start_date).toLocaleDateString('en-IN')}</small>
                    ${isDiscontinued ? '<br><small class="text-danger">DISCONTINUED</small>' : ''}
                </td>
              
                <td>
                    <strong>₹${monthlyAmount.toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong>
                </td>
                <td class="text-center">
                    <span class="${installmentsPaid > 0 ? 'text-success fw-bold' : 'text-muted'}">
                        ${installmentsPaid}
                    </span>
                </td>
                <td class="text-center">
                    <span class="${missedMonths >= 4 ? 'text-danger fw-bold' : missedMonths > 0 ? 'text-warning' : 'text-success'}">
                        ${missedMonths}
                    </span>
                    ${missedMonths > 0 ? `<br><small class="text-muted">₹${penaltyPerMonth.toFixed(2)}/month</small>` : ''}
                </td>
                <td>
                    <span class="${accountPenalty > 0 ? 'text-danger' : 'text-muted'}">
                        ₹${accountPenalty.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                    </span>
                </td>
                <td>
                    <strong class="${accountPayable > 0 ? 'text-danger' : 'text-success'}">
                        ₹${accountPayable.toLocaleString('en-IN', {minimumFractionDigits: 2})}
                    </strong>
                </td>
                <td>
                    <span class="badge bg-${statusColor}">
                        ${computedStatus.charAt(0).toUpperCase() + computedStatus.slice(1)}
                    </span>
                </td>
            </tr>
        `;
    });
    
    $('#accounts-table-body').html(tableHtml);
    
    // Update total row with penalty-based calculations
    $('#total-monthly').text(`₹${totalMonthly.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#total-penalty').text(`₹${totalPenalty.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#total-payable').text(`₹${totalPayable.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#collection-amount-btn').text(`₹${totalPayable.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#summary-pending').text(`₹${totalPayable.toLocaleString('en-IN')}`);
    
    // Store total payable for collection form
    window.totalPendingAmount = totalPayable;
    window.currentAccounts = accounts;
    
    // Show total row and collection button
    $('#total-row, #collection-row').show();
}

// Collection Form Functions
function openCollectionForm() {
    if (!selectedCustomer || !window.currentAccounts) {
        alert('Please select a customer first');
        return;
    }
    
    // Calculate totals
    let totalMonthly = 0;
    let totalPending = window.totalPendingAmount || 0;
    let activeAccounts = 0;
    
    window.currentAccounts.forEach(account => {
        if (account.status === 'active') {
            totalMonthly += parseFloat(account.monthly_amount || 0);
            activeAccounts++;
        }
    });
    
    // Update collection form info
    $('#collection-customer-name').text(selectedCustomer.name);
    $('#collection-total-accounts').text(`${activeAccounts} Active`);
    $('#collection-pending-amount').text(`₹${totalPending.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#amount-helper-pending').text(`₹${totalPending.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    $('#amount-helper-monthly').text(`₹${totalMonthly.toLocaleString('en-IN', {minimumFractionDigits: 2})}`);
    
    // Set customer ID
    $('#selected-customer-id').val(selectedCustomer.id);
    
    // Set default amount to pending amount
    $('#amount').val(totalPending);
    
    // Store totals for helper functions
    window.totalMonthlyAmount = totalMonthly;
    
    // Update step indicator
    updateStepIndicator(2);
    
    // Auto-generate receipt number when form opens
    generateReceiptNumber();
    
    // Show collection form
    $('#collection-form-section').show();
    
    // Scroll to collection form
    $('html, body').animate({
        scrollTop: $('#collection-form-section').offset().top - 100
    }, 500);
}

function closeCollectionForm() {
    $('#collection-form-section').hide();
    updateStepIndicator(1);
    
    // Clear form
    $('#collection-entry-form')[0].reset();
    
    // Scroll back to accounts table
    $('html, body').animate({
        scrollTop: $('#accounts-table-section').offset().top - 100
    }, 500);
}

function setAmountToPending() {
    $('#amount').val(window.totalPendingAmount || 0);
    $('#amount').focus();
}

function setAmountToMonthly() {
    $('#amount').val(window.totalMonthlyAmount || 0);
    $('#amount').focus();
}

function updateStepIndicator(activeStep) {
    // Reset all steps
    $('.step').removeClass('active completed').addClass('inactive');
    
    // Mark completed steps
    for (let i = 1; i < activeStep; i++) {
        $(`#step-${i}`).removeClass('inactive').addClass('completed');
    }
    
    // Mark active step
    $(`#step-${activeStep}`).removeClass('inactive').addClass('active');
}

function clearCustomerSearch() {
    $('#search-name, #search-mobile, #search-cif').val('');
    $('#customer-results').hide();
    $('#accounts-table-section').hide();
    $('#collection-form-section').hide();
    updateStepIndicator(1);
    selectedCustomer = null;
    window.currentAccounts = null;
    window.totalPendingAmount = 0;
    window.totalMonthlyAmount = 0;
}

function goBackToCustomerSearch() {
    $('#accounts-table-section').hide();
    $('#collection-form-section').hide();
    updateStepIndicator(1);
    selectedCustomer = null;
    window.currentAccounts = null;
    window.totalPendingAmount = 0;
    window.totalMonthlyAmount = 0;
}

// Form validation
$('#collection-entry-form').on('submit', function(e) {
    const amount = parseFloat($('#amount').val());
    const totalMonthly = window.totalMonthlyAmount || 0;
    const totalPending = window.totalPendingAmount || 0;
    
    if (!selectedCustomer) {
        alert('Please select a customer first');
        e.preventDefault();
        return false;
    }
    
    if (amount <= 0) {
        alert('Please enter a valid collection amount');
        e.preventDefault();
        return false;
    }
    
    // Validation for high amounts
    if (amount > totalMonthly * 3) {
        if (!confirm(`The entered amount (₹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}) is significantly higher than the total monthly amount (₹${totalMonthly.toLocaleString('en-IN', {minimumFractionDigits: 2})}). Do you want to continue?`)) {
            e.preventDefault();
            return false;
        }
    }
    
    // Validation for very low amounts
    if (amount < totalMonthly * 0.1) {
        if (!confirm(`The entered amount (₹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})}) is very low compared to the total monthly amount (₹${totalMonthly.toLocaleString('en-IN', {minimumFractionDigits: 2})}). Do you want to continue?`)) {
            e.preventDefault();
            return false;
        }
    }
    
    // Show confirmation for collection
    const accountsCount = window.currentAccounts ? window.currentAccounts.filter(a => a.status === 'active').length : 0;
    if (!confirm(`Are you sure you want to submit collection of ₹${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})} for ${accountsCount} accounts of ${selectedCustomer.name}?`)) {
        e.preventDefault();
        return false;
    }
});

// Payment type change handler
$('#payment-type').change(function() {
    const paymentType = $(this).val();
    
    // Show/hide additional fields based on payment type
    if (paymentType === 'cheque') {
        // Could add cheque-specific fields in future
    } else if (paymentType === 'upi') {
        // Could add UPI-specific fields in future
    }
});

// Receipt Number Generation Function
function generateReceiptNumber() {
    const now = new Date();
    
    // Get agent mobile number from auth user
    const agentMobile = '{{ auth()->user()->mobile_number ?? "0000000000" }}';
    
    // Extract first 3 and last digit from mobile number
    const mobilePrefix = agentMobile.substring(0, 3); // First 3 digits
    const mobileSuffix = agentMobile.substring(agentMobile.length - 1); // Last digit
    const mobileCode = mobilePrefix + mobileSuffix; // e.g., "9871" for mobile "9876543210"
    
    // Format: XXX[LastDigit]-YYMMDD-HHMMSS
    const year = now.getFullYear().toString().substr(-2); // Last 2 digits of year
    const month = String(now.getMonth() + 1).padStart(2, '0'); // Month (01-12)
    const day = String(now.getDate()).padStart(2, '0'); // Day (01-31)
    const hour = String(now.getHours()).padStart(2, '0'); // Hour (00-23)
    const minute = String(now.getMinutes()).padStart(2, '0'); // Minute (00-59)
    const second = String(now.getSeconds()).padStart(2, '0'); // Second (00-59)
    
    const receiptNumber = `${mobileCode}-${year}${month}${day}-${hour}${minute}${second}`;
    
    $('#receipt-number').val(receiptNumber);
    
    // Show success feedback
    const generateBtn = $('#generate-receipt-btn');
    const originalHtml = generateBtn.html();
    generateBtn.html('<i class="fas fa-check me-1"></i>Generated').addClass('btn-success').removeClass('btn-outline-primary');
    
    setTimeout(() => {
        generateBtn.html(originalHtml).removeClass('btn-success').addClass('btn-outline-primary');
    }, 1500);
    
    console.log('Generated receipt number:', receiptNumber);
    console.log('Agent mobile:', agentMobile, 'Code:', mobileCode);
    return receiptNumber;
}

// Debug function to show all customers
function debugShowAllCustomers() {
    console.log('Debug: Loading all customers for agent');
    
    $('#customer-list').html(`
        <div class="col-12 text-center">
            <div class="spinner-border text-info" role="status">
                <span class="visually-hidden">Loading all customers...</span>
            </div>
            <p class="mt-2 text-muted">Loading all your customers...</p>
        </div>
    `);
    $('#customer-results').show();
    
    // Make AJAX request to get all customers
    $.ajax({
        url: '{{ route("collections.create") }}',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        data: {
            debug_all_customers: 1
        },
        success: function(response) {
            console.log('Debug All Customers Response:', response);
            if (response.success) {
                if (response.customers.length > 0) {
                    displayCustomers(response.customers);
                    alert(`Debug: Found ${response.customers.length} customers for your agent account`);
                } else {
                    $('#customer-list').html(`
                        <div class="col-12 text-center">
                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                No customers found for your agent account. Please contact admin to assign customers to you.
                            </div>
                        </div>
                    `);
                }
            }
        },
        error: function(xhr, status, error) {
            console.error('Debug Error:', error);
            $('#customer-list').html(`
                <div class="col-12 text-center">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Debug Error: ${error}
                    </div>
                </div>
            `);
        }
    });
}
</script>
@endpush
@endsection
