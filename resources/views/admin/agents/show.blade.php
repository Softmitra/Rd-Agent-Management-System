@extends('layouts.app')

@section('title', 'View Agent')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1>View Agent</h1>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('agents.index') }}" class="btn btn-default">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
                <a href="{{ route('agents.edit', $agent) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit Agent
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <!-- Agent Details Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Agent Details</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Agent ID</label>
                                <p class="form-control-static">{{ $agent->agent_id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Name</label>
                                <p class="form-control-static">{{ $agent->name }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <p class="form-control-static">{{ $agent->email }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Branch</label>
                                <p class="form-control-static">{{ $agent->branch }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Aadhar Number</label>
                                <p class="form-control-static">{{ $agent->aadhar_number }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>PAN Number</label>
                                <p class="form-control-static">{{ $agent->pan_number }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Contact Phone</label>
                                <p class="form-control-static">{{ $agent->contact_info['phone'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Address</label>
                                <p class="form-control-static">{{ $agent->contact_info['address'] ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Documents</label>
                                <div class="row">
                                    @if($agent->aadhar_file)
                                        <div class="col-md-6">
                                            <div class="card document-card">
                                                <div class="card-header">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-id-card text-primary"></i> Aadhar Card
                                                    </h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <div class="document-preview mb-3">
                                                        @php
                                                            $fileExtension = pathinfo($agent->aadhar_file, PATHINFO_EXTENSION);
                                                            $isPdf = strtolower($fileExtension) === 'pdf';
                                                        @endphp
                                                        @if($isPdf)
                                                            <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                                            <p class="mt-2 text-muted">PDF Document</p>
                                                        @else
                                                            <img src="{{ Storage::url($agent->aadhar_file) }}" alt="Aadhar Card" class="img-thumbnail" style="max-height: 120px; cursor: pointer;" onclick="viewDocument('{{ route('agents.document', [$agent, 'aadhar']) }}', 'Aadhar Card', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="viewDocument('{{ route('agents.document', [$agent, 'aadhar']) }}', 'Aadhar Card', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                        <i class="fas fa-eye"></i> View Document
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if($agent->pan_file)
                                        <div class="col-md-6">
                                            <div class="card document-card">
                                                <div class="card-header">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-id-card text-success"></i> PAN Card
                                                    </h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    <div class="document-preview mb-3">
                                                        @php
                                                            $fileExtension = pathinfo($agent->pan_file, PATHINFO_EXTENSION);
                                                            $isPdf = strtolower($fileExtension) === 'pdf';
                                                        @endphp
                                                        @if($isPdf)
                                                            <i class="fas fa-file-pdf fa-4x text-danger"></i>
                                                            <p class="mt-2 text-muted">PDF Document</p>
                                                        @else
                                                            <img src="{{ Storage::url($agent->pan_file) }}" alt="PAN Card" class="img-thumbnail" style="max-height: 120px; cursor: pointer;" onclick="viewDocument('{{ route('agents.document', [$agent, 'pan']) }}', 'PAN Card', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                        @endif
                                                    </div>
                                                    <button type="button" class="btn btn-success btn-sm" onclick="viewDocument('{{ route('agents.document', [$agent, 'pan']) }}', 'PAN Card', '{{ $isPdf ? 'pdf' : 'image' }}')">
                                                        <i class="fas fa-eye"></i> View Document
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!$agent->aadhar_file && !$agent->pan_file)
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i> No documents uploaded yet.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Account Status</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Verification Status</label>
                        <div>
                            @if($agent->is_verified)
                                <span class="badge badge-success">Verified</span>
                                @if($agent->verified_at)
                                <p class="text-muted mt-1">
                                    Verified on {{ $agent->verified_at->format('M d, Y h:i A') }}
                                    @if($agent->verifier)
                                        by {{ $agent->verifier->name }}
                                    @endif
                                </p>
                                @endif
                                @if($agent->verification_remarks)
                                    <p class="text-muted">
                                        Remarks: {{ $agent->verification_remarks }}
                                    </p>
                                @endif
                            @else
                                <span class="badge badge-warning">Pending Verification</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Account Status</label>
                        <div>
                            @if($agent->is_active)
                                <span class="badge badge-info">Active</span>
                                @if($agent->account_expires_at)
                                    <p class="text-muted mt-1">
                                        Expires on {{ $agent->account_expires_at->format('M d, Y') }}
                                    </p>
                                @endif
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if(auth()->user()->hasRole('admin'))
                        <hr>
                        <div class="form-group">
                            <label>Actions</label>
                            <div>
                                @if(!$agent->is_verified)
                                    <button type="button" class="btn btn-success verify-agent mb-2 btn-block">
                                        <i class="fas fa-check"></i> Verify Agent
                                    </button>
                                @else
                                    <form action="{{ route('agents.unverify', $agent) }}" method="POST" class="mb-2">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-times"></i> Unverify Agent
                                        </button>
                                    </form>
                                @endif

                                @if($agent->is_active)
                                    <form action="{{ route('agents.deactivate', $agent) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="fas fa-ban"></i> Deactivate Account
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn btn-success activate-agent btn-block">
                                        <i class="fas fa-check-circle"></i> Activate Account
                                    </button>
                                @endif

                                @if($agent->is_active)
                                    <button type="button" class="btn btn-info update-expiration btn-block mt-2">
                                        <i class="fas fa-calendar-alt"></i> Update Expiration Date
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Timestamps</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Created At</label>
                        <p class="form-control-static">{{ $agent->created_at ? $agent->created_at->format('M d, Y h:i A') : 'N/A' }}</p>
                    </div>
                    <div class="form-group">
                        <label>Last Updated</label>
                        <p class="form-control-static">{{ $agent->updated_at ? $agent->updated_at->format('M d, Y h:i A') : 'N/A' }}</p>
                    </div>
                    @if($agent->email_verified_at)
                        <div class="form-group">
                            <label>Email Verified At</label>
                            <p class="form-control-static">{{ $agent->email_verified_at->format('M d, Y h:i A') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verify Agent Modal -->
<div class="modal fade" id="verifyAgentModal" tabindex="-1" role="dialog" aria-labelledby="verifyAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('agents.verify', $agent) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="verifyAgentModalLabel">Verify Agent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="remarks">Verification Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Enter any remarks about the verification"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Verify Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activate Agent Modal -->
<div class="modal fade" id="activateAgentModal" tabindex="-1" role="dialog" aria-labelledby="activateAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('agents.activate', $agent) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="activateAgentModalLabel">Activate Agent</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="expires_at">Account Expiry Date</label>
                        <input type="date" class="form-control" id="expires_at" name="expires_at">
                        <small class="form-text text-muted">Leave blank for no expiry date</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Activate Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Expiration Modal -->
<div class="modal fade" id="updateExpirationModal" tabindex="-1" role="dialog" aria-labelledby="updateExpirationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('agents.update-expiration', $agent) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="updateExpirationModalLabel">Update Expiration Date</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="update_expires_at">Account Expiry Date</label>
                        <input type="date" class="form-control" id="update_expires_at" name="expires_at" 
                               value="{{ $agent->account_expires_at ? $agent->account_expires_at->format('Y-m-d') : '' }}">
                        <small class="form-text text-muted">Leave blank for no expiry date</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Expiration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="documentViewerModal" tabindex="-1" role="dialog" aria-labelledby="documentViewerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentViewerModalLabel">Document Viewer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" style="min-height: 500px;">
                <div id="documentContent">
                    <!-- Document content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="#" target="_blank" class="btn btn-primary">
                    <i class="fas fa-download"></i> Download
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function() {
    // Handle verify agent modal
    $('.verify-agent').click(function(e) {
        e.preventDefault();
        $('#verifyAgentModal').modal('show');
    });

    // Handle activate agent modal
    $('.activate-agent').click(function(e) {
        e.preventDefault();
        $('#activateAgentModal').modal('show');
    });
    
    // Handle update expiration modal
    $('.update-expiration').click(function(e) {
        e.preventDefault();
        $('#updateExpirationModal').modal('show');
    });
});

// Function to view documents
function viewDocument(url, title, type) {
    $('#documentViewerModalLabel').text(title);
    $('#downloadLink').attr('href', url);
    
    if (type === 'pdf') {
        $('#documentContent').html(`
            <embed src="${url}" type="application/pdf" width="100%" height="500px" style="border: none;">
            <p class="mt-3 text-muted">If the PDF doesn't load, <a href="${url}" target="_blank">click here to open in a new tab</a></p>
        `);
    } else {
        $('#documentContent').html(`
            <img src="${url}" alt="${title}" class="img-fluid" style="max-width: 100%; max-height: 70vh; border: 1px solid #ddd; border-radius: 4px;">
        `);
    }
    
    $('#documentViewerModal').modal('show');
}
</script>

<style>
.document-card {
    margin-bottom: 20px;
    transition: transform 0.2s ease-in-out;
    border: 1px solid #e3e6f0;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.document-preview {
    min-height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.document-card .card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
}

.img-thumbnail {
    border-radius: 8px;
    transition: transform 0.2s ease-in-out;
}

.img-thumbnail:hover {
    transform: scale(1.05);
}
</style>
@endpush
@endsection 