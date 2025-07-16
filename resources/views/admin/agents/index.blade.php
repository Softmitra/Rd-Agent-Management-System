@extends('layouts.app')

@section('title', 'Manage Agents')

@section('content')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h5>Manage Agents</h5>
        </div>
        <div class="col-sm-6">
            <div class="float-sm-right">
                <a href="{{ route('agents.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Agent
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Agents</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Agent ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Branch</th>
                            <th>Status</th>
                            <th>Documents</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td>{{ $agent->agent_id }}</td>
                                <td>{{ $agent->name }}</td>
                                <td>{{ $agent->email }}</td>
                                <td>{{ $agent->branch }}</td>
                                <td>
                                    <div class="mb-1">
                                        @if($agent->is_verified)
                                            <span class="badge badge-success">Verified</span>
                                        @else
                                            <span class="badge badge-warning">Pending Verification</span>
                                        @endif
                                    </div>
                                    <div>
                                        @if($agent->is_active)
                                            <span class="badge badge-info">Active</span>
                                            @if($agent->account_expires_at)
                                                <br>
                                                <small class="text-muted">
                                                    Expires: {{ $agent->account_expires_at->format('d/m/Y') }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($agent->aadhar_file)
                                        <a href="{{ Storage::url($agent->aadhar_file) }}" target="_blank" class="btn btn-sm btn-info mb-1">
                                            <i class="fas fa-id-card"></i> Aadhar
                                        </a>
                                    @endif
                                    @if($agent->pan_file)
                                        <a href="{{ Storage::url($agent->pan_file) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-id-card"></i> PAN
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('agents.show', $agent) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('agents.edit', $agent) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        @if(auth()->user()->hasRole('admin'))
                                            @if(!$agent->is_verified)
                                                <form action="{{ route('agents.verify', $agent) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Verify Agent">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('agents.unverify', $agent) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning btn-sm" title="Unverify Agent">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($agent->is_active)
                                                <form action="{{ route('agents.deactivate', $agent) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Deactivate Agent">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('agents.activate', $agent) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm" title="Activate Agent">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($agent->is_active)
                                                <button type="button" class="btn btn-info btn-sm update-expiration" 
                                                        data-agent-id="{{ $agent->id }}" 
                                                        data-expires-at="{{ $agent->account_expires_at ? $agent->account_expires_at->format('Y-m-d') : '' }}"
                                                        title="Update Expiration Date">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No agents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Verify Agent Modal -->
<div class="modal fade" id="verifyAgentModal" tabindex="-1" role="dialog" aria-labelledby="verifyAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="verifyAgentForm" method="POST">
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
            <form id="activateAgentForm" method="POST">
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
            <form id="updateExpirationForm" method="POST">
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
                        <input type="date" class="form-control" id="update_expires_at" name="expires_at">
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

@push('scripts')
<script>
$(function() {
    // Handle verify agent modal
    $('.verify-agent').click(function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        $('#verifyAgentForm').attr('action', form.attr('action'));
        $('#verifyAgentModal').modal('show');
    });

    // Handle activate agent modal
    $('.activate-agent').click(function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        $('#activateAgentForm').attr('action', form.attr('action'));
        $('#activateAgentModal').modal('show');
    });
    
    // Handle update expiration modal
    $('.update-expiration').click(function(e) {
        e.preventDefault();
        var agentId = $(this).data('agent-id');
        var expiresAt = $(this).data('expires-at');
        
        $('#updateExpirationForm').attr('action', '/admin/agents/' + agentId + '/update-expiration');
        $('#update_expires_at').val(expiresAt);
        $('#updateExpirationModal').modal('show');
    });
});
</script>
@endpush
@endsection 