@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">
            <i class="fas fa-file-excel text-success me-2"></i>Excel Import - RD Accounts
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Excel Import</li>
            </ol>
        </nav>
    </div>

    <!-- Instructions Card -->
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Import Instructions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="fw-bold">Excel Format Requirements:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> Column A: E-Banking Ref No</li>
                        <li><i class="fas fa-check text-success"></i> Column B: RD Account Number</li>
                        <li><i class="fas fa-check text-success"></i> Column C: Account Name <span class="text-danger">*</span></li>
                        <li><i class="fas fa-check text-success"></i> Column D: RD Denomination <span class="text-danger">*</span></li>
                        <li><i class="fas fa-check text-success"></i> Column E: RD Total Deposit Amount</li>
                        <li><i class="fas fa-check text-success"></i> Column F: No of Installments</li>
                    </ul>
                    <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-warning">
                        <h6 class="fw-bold">Important Notes:</h6>
                        <ul class="mb-0">
                            <li>System automatically detects data start row</li>
                            <li>Only <strong>Name</strong> and <strong>Amount</strong> are required for import</li>
                            <li>Duplicate customers will be flagged for agent assignment</li>
                            <li>Missing phone numbers will use placeholder (0000000000)</li>
                            <li>Bank details and rebate/fees will be ignored</li>
                        </ul>
                        <div class="alert alert-info mt-2 mb-0">
                            <strong>📝 Post-Import Action Required:</strong><br>
                            After import, agents must manually update:
                            <ul class="mb-0 mt-1">
                                <li>Customer phone numbers (currently 0000000000)</li>
                                <li>Customer addresses and contact details</li>
                                <li>Account duration and maturity dates if different</li>
                                <li>Interest rates if not 6.5%</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-upload"></i> Upload Excel File</h5>
        </div>
        <div class="card-body">
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">Select Excel File</label>
                            <input type="file" class="form-control" id="excel_file" name="excel_file" 
                                   accept=".xlsx,.xls,.csv" required>
                            <div class="form-text">Supported formats: Excel (.xlsx, .xls) and CSV (.csv). Max size: 10MB</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block" id="uploadBtn">
                                <i class="fas fa-cloud-upload-alt"></i> Upload & Preview
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="card d-none">
        <div class="card-body text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Processing Excel file...</p>
        </div>
    </div>

    <!-- Preview Results -->
    <div id="previewResults" class="d-none">
        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div>
                                <div class="fs-6">Valid Records</div>
                                <div class="fs-2 fw-bold" id="validCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <div>
                                <div class="fs-6">Duplicates</div>
                                <div class="fs-2 fw-bold" id="duplicateCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <div>
                                <div class="fs-6">Errors</div>
                                <div class="fs-2 fw-bold" id="errorCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <div>
                                <div class="fs-6">Total Records</div>
                                <div class="fs-2 fw-bold" id="totalCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Duplicate Customers Handling -->
        <div id="duplicatesSection" class="card mb-4 d-none">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Duplicate Customers - Cannot Import</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    The following customers already exist in the system. Duplicate entries will not be imported to maintain data integrity.
                </div>
                <div class="table-responsive">
                    <table class="table table-striped" id="duplicatesTable">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Customer Name</th>
                                <th>New RD Amount</th>
                                <th>Existing RD Accounts</th>
                            </tr>
                        </thead>
                        <tbody id="duplicatesTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-lightbulb me-2"></i>
                    <strong>Note:</strong> These duplicate entries will be automatically skipped during import. 
                    If you need to add additional RD accounts for existing customers, please use the regular account creation process after import.
                </div>
            </div>
        </div>

        <!-- Errors Section -->
        <div id="errorsSection" class="card mb-4 d-none">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-exclamation-circle"></i> Data Errors</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Error</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody id="errorsTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Import Actions -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">
                        <i class="fas fa-undo"></i> Upload New File
                    </button>
                    <button type="button" class="btn btn-success btn-lg" id="importBtn" onclick="startImport()">
                        <i class="fas fa-database"></i> Start Import Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Import Progress -->
    <div id="importProgress" class="card d-none">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-cogs"></i> Import in Progress</h5>
        </div>
        <div class="card-body">
            <div class="progress mb-3">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" 
                     style="width: 100%" id="importProgressBar"></div>
            </div>
            <p class="text-center mb-0">Importing data, please wait...</p>
        </div>
    </div>

    <!-- Import Results -->
    <div id="importResults" class="card d-none">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-check-circle"></i> Import Complete</h5>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border-end">
                        <div class="fs-2 fw-bold text-success" id="importedCount">0</div>
                        <div>Imported</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <div class="fs-2 fw-bold text-warning" id="skippedCount">0</div>
                        <div>Skipped</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <div class="fs-2 fw-bold text-danger" id="finalErrorCount">0</div>
                        <div>Errors</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="fs-2 fw-bold text-info" id="finalTotalCount">0</div>
                    <div>Total Processed</div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('admin.rd-accounts.index') }}" class="btn btn-primary">
                    <i class="fas fa-list"></i> View RD Accounts
                </a>
                <button type="button" class="btn btn-success" onclick="resetForm()">
                    <i class="fas fa-plus"></i> Import Another File
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
let previewData = null;
let agents = [];

// Load agents on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAgents();
});

async function loadAgents() {
    try {
        const response = await axios.get('{{ route("admin.excel-import.agents") }}');
        agents = response.data;
    } catch (error) {
        console.error('Error loading agents:', error);
        showAlert('Error loading agents. Please refresh the page.', 'danger');
    }
}

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('excel_file');
    const file = fileInput.files[0];
    
    if (!file) {
        showAlert('Please select a file to upload.', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('excel_file', file);
    formData.append('_token', document.querySelector('[name="_token"]').value);
    
    showLoading(true);
    hidePreview();
    
    try {
        const response = await axios.post('{{ route("admin.excel-import.upload") }}', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        if (response.data.success) {
            previewData = response.data;
            showPreview(response.data);
            showAlert('File processed successfully. Review the data below.', 'success');
        } else {
            throw new Error(response.data.message || 'Upload failed');
        }
    } catch (error) {
        console.error('Upload error:', error);
        const message = error.response?.data?.message || error.message || 'Upload failed';
        showAlert(message, 'danger');
    } finally {
        showLoading(false);
    }
});

function showLoading(show) {
    const loading = document.getElementById('loadingIndicator');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (show) {
        loading.classList.remove('d-none');
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    } else {
        loading.classList.add('d-none');
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-cloud-upload-alt"></i> Upload & Preview';
    }
}

function showPreview(data) {
    // Update summary cards
    document.getElementById('validCount').textContent = data.total_records;
    document.getElementById('duplicateCount').textContent = data.duplicates;
    document.getElementById('errorCount').textContent = data.errors;
    document.getElementById('totalCount').textContent = data.total_records + data.duplicates + data.errors;
    
    // Show duplicates section if any
    if (data.duplicates > 0) {
        populateDuplicatesTable(data.data.duplicates);
        document.getElementById('duplicatesSection').classList.remove('d-none');
    }
    
    // Show errors section if any
    if (data.errors > 0) {
        populateErrorsTable(data.data.errors);
        document.getElementById('errorsSection').classList.remove('d-none');
    }
    
    // Show preview results
    document.getElementById('previewResults').classList.remove('d-none');
}

function populateDuplicatesTable(duplicates) {
    const tbody = document.getElementById('duplicatesTableBody');
    tbody.innerHTML = '';
    
    duplicates.forEach((duplicate, index) => {
        const row = document.createElement('tr');
        
        // Set row styling based on duplicate type
        const canImport = duplicate.can_import || false;
        const duplicateType = duplicate.duplicate_type || 'exact_duplicate';
        
        if (!canImport) {
            row.classList.add('table-danger'); // Red background for exact duplicates
        } else {
            row.classList.add('table-warning'); // Yellow background for allowed duplicates
        }
        
        // Create existing RD accounts list
        let existingRdAccountsHtml = '<span class="text-muted">Loading...</span>';
        
        // Create status badge
        let statusBadge = '';
        if (duplicateType === 'exact_duplicate') {
            statusBadge = '<span class="badge bg-danger">Cannot Import</span>';
        } else {
            statusBadge = '<span class="badge bg-success">Can Import</span>';
        }
        
        row.innerHTML = `
            <td>${duplicate.row}</td>
            <td>
                <strong>${duplicate.customer_name}</strong>
                <div class="small text-muted">
                    Agent: <span class="badge bg-info">${duplicate.existing_agent}</span>
                    <br>Customer ID: ${duplicate.existing_customer_id}
                </div>
            </td>
            <td>
                <span class="badge bg-warning">₹${duplicate.data.rd_denomination.toFixed(2)}</span>
            </td>
            <td class="existing-rd-accounts" data-customer-id="${duplicate.existing_customer_id}">
                ${existingRdAccountsHtml}
                <div class="mt-2">
                    ${statusBadge}
                    <br><small class="text-muted">${duplicate.remark || 'Processing...'}</small>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
        
        // Load existing RD accounts for this customer
        loadExistingRdAccounts(duplicate.existing_customer_id);
    });
}

// Function to load existing RD accounts for a customer
async function loadExistingRdAccounts(customerId) {
    try {
        // Fetch RD accounts for this customer using admin route
        const response = await axios.get(`{{ url('admin/customers') }}/${customerId}/rd-accounts`);
        const rdAccounts = response.data;
        
        let rdAccountsHtml = '';
        if (rdAccounts.length === 0) {
            rdAccountsHtml = '<span class="text-muted">No RD accounts found</span>';
        } else {
            rdAccounts.forEach(account => {
                const statusClass = account.status === 'active' ? 'bg-success' : 
                                   account.status === 'matured' ? 'bg-warning' : 'bg-secondary';
                rdAccountsHtml += `
                    <div class="mb-1">
                        <small>
                            <strong>${account.account_number}</strong><br>
                            Amount: <span class="badge ${statusClass}">₹${parseFloat(account.monthly_amount).toFixed(0)}</span>
                            Status: <span class="badge ${statusClass}">${account.status.toUpperCase()}</span>
                        </small>
                    </div>
                `;
            });
        }
        
        // Update the table cell
        const cell = document.querySelector(`[data-customer-id="${customerId}"]`);
        if (cell) {
            cell.innerHTML = rdAccountsHtml;
        }
        
    } catch (error) {
        console.error('Error loading RD accounts:', error);
        const cell = document.querySelector(`[data-customer-id="${customerId}"]`);
        if (cell) {
            cell.innerHTML = '<span class="text-danger">Error loading accounts</span>';
        }
    }
}

function populateErrorsTable(errors) {
    const tbody = document.getElementById('errorsTableBody');
    tbody.innerHTML = '';
    
    errors.forEach(error => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${error.row}</td>
            <td><span class="text-danger">${error.error}</span></td>
            <td><small class="text-muted">${JSON.stringify(error.data).substring(0, 100)}...</small></td>
        `;
        tbody.appendChild(row);
    });
}

async function startImport() {
    if (!previewData) {
        showAlert('No data to import. Please upload a file first.', 'warning');
        return;
    }
    
    // Show confirmation dialog if there are duplicates
    if (previewData.duplicates > 0) {
        if (!confirm(`This import contains ${previewData.duplicates} duplicate customers that will be automatically skipped. Do you want to proceed with importing only the new customers?`)) {
            return;
        }
    }
    
    // Show import progress
    document.getElementById('previewResults').classList.add('d-none');
    document.getElementById('importProgress').classList.remove('d-none');
    
    try {
        const response = await axios.post('{{ route("admin.excel-import.import") }}', {
            file_path: previewData.file_path,
            agent_assignments: {}, // No agent assignments needed - duplicates are automatically skipped
            skip_duplicates: true, // Always skip duplicates
            _token: document.querySelector('[name="_token"]').value
        });
        
        if (response.data.success) {
            showImportResults(response.data.results);
            showAlert('Import completed successfully!', 'success');
        } else {
            throw new Error(response.data.message || 'Import failed');
        }
    } catch (error) {
        console.error('Import error:', error);
        const message = error.response?.data?.message || error.message || 'Import failed';
        showAlert(message, 'danger');
        
        // Show preview again
        document.getElementById('importProgress').classList.add('d-none');
        document.getElementById('previewResults').classList.remove('d-none');
    }
}

function showImportResults(results) {
    document.getElementById('importProgress').classList.add('d-none');
    
    // Update result counts
    document.getElementById('importedCount').textContent = results.imported;
    document.getElementById('skippedCount').textContent = results.skipped;
    document.getElementById('finalErrorCount').textContent = results.errors;
    document.getElementById('finalTotalCount').textContent = results.total;
    
    document.getElementById('importResults').classList.remove('d-none');
}

function hidePreview() {
    document.getElementById('previewResults').classList.add('d-none');
    document.getElementById('duplicatesSection').classList.add('d-none');
    document.getElementById('errorsSection').classList.add('d-none');
    document.getElementById('importResults').classList.add('d-none');
}

function resetForm() {
    document.getElementById('uploadForm').reset();
    hidePreview();
    previewData = null;
    
    // Remove any existing alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (!alert.classList.contains('alert-warning')) { // Keep instructions alert
            alert.remove();
        }
    });
}

function showAlert(message, type) {
    // Remove existing alerts first
    const existingAlerts = document.querySelectorAll('.alert:not(.alert-warning)');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert after the title
    const container = document.querySelector('.container-fluid');
    const title = container.querySelector('h1').parentElement;
    if (title) {
        title.insertAdjacentElement('afterend', alertDiv);
    } else {
        // Fallback: insert at the beginning of the container
        container.insertAdjacentElement('afterbegin', alertDiv);
    }
    
    // Auto dismiss success alerts
    if (type === 'success') {
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
}
</script>
@endpush
@endsection
