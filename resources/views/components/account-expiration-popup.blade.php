@if(session()->has('account_status'))
    @php
        $status = session('account_status');
        $isExpired = $status['type'] === 'expired';
    @endphp
    
    <div class="modal fade" id="accountExpirationModal" tabindex="-1" aria-labelledby="accountExpirationModalLabel" aria-hidden="true" 
         data-bs-backdrop="{{ $isExpired ? 'static' : 'true' }}" data-bs-keyboard="{{ $isExpired ? 'false' : 'true' }}">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header {{ $isExpired ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                    <h5 class="modal-title" id="accountExpirationModalLabel">
                        <i class="fas fa-{{ $isExpired ? 'exclamation-triangle' : 'clock' }} me-2"></i>
                        {{ $isExpired ? 'Account Expired' : 'Account Expiring Soon' }}
                    </h5>
                    @if(!$isExpired)
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    @endif
                </div>
                <div class="modal-body">
                    <p>{{ $status['message'] }}</p>
                    
                    @if($isExpired)
                        <p class="text-danger">
                            <strong>Expired on:</strong> {{ $status['expired_at'] }}
                        </p>
                    @else
                        <p class="text-warning">
                            <strong>Expires on:</strong> {{ $status['expires_at'] }}
                            <br>
                            <strong>Days remaining:</strong> {{ $status['days_remaining'] }}
                        </p>
                    @endif
                    
                    <div class="alert alert-info mt-3">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Contact Administrator</h6>
                        <p class="mb-0">Please contact the system administrator to {{ $isExpired ? 'reactivate' : 'extend' }} your account.</p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Email:</strong> admin@rdagent.com
                            </div>
                            <div>
                                <strong>Phone:</strong> +91 1234567890
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if(!$isExpired)
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    @endif
                    <a href="mailto:admin@rdagent.com" class="btn btn-primary">
                        <i class="fas fa-envelope me-2"></i>Email Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    @push('styles')
    <style>
        @if($isExpired)
        /* Darker backdrop for expired accounts */
        .modal-backdrop.show {
            opacity: 0.8;
        }
        
        /* Disable scrolling when modal is open */
        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
        }
        
        /* Make the modal more prominent */
        #accountExpirationModal .modal-content {
            border: 3px solid #dc3545;
            box-shadow: 0 0 20px rgba(220, 53, 69, 0.5);
        }
        @endif
    </style>
    @endpush
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var accountExpirationModal = new bootstrap.Modal(document.getElementById('accountExpirationModal'));
            accountExpirationModal.show();
            
            @if($isExpired)
            // Prevent clicking outside the modal
            document.getElementById('accountExpirationModal').addEventListener('hide.bs.modal', function (event) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            });
            
            // Disable all links and buttons outside the modal
            document.body.addEventListener('click', function(event) {
                if (!event.target.closest('#accountExpirationModal')) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, true);
            
            // Additional code to prevent modal from being dismissed
            var modalElement = document.getElementById('accountExpirationModal');
            
            // Prevent ESC key from closing the modal
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
            
            // Prevent clicking on backdrop from closing the modal
            modalElement.addEventListener('click', function(event) {
                if (event.target === modalElement) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            });
            
            // Override the bootstrap modal's hide method
            var originalHide = bootstrap.Modal.prototype.hide;
            bootstrap.Modal.prototype.hide = function() {
                if (this._element.id === 'accountExpirationModal' && @json($isExpired)) {
                    return false;
                }
                return originalHide.apply(this, arguments);
            };
            @endif
        });
    </script>
    @endpush
@endif 