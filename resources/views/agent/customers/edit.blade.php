@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Customer - {{ $customer->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('agent.customers.show', $customer) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Customer
                        </a>
                        <a href="{{ route('agent.customers.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Customers
                        </a>
                    </div>
                </div>

                <form action="{{ route('agent.customers.update', $customer) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Basic Information</h5>
                                
                                <div class="form-group">
                                    <label for="name" class="required">Customer Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="mobile_number" class="required">Mobile Number</label>
                                    <input type="tel" class="form-control @error('mobile_number') is-invalid @enderror" 
                                           id="mobile_number" name="mobile_number" value="{{ old('mobile_number', $customer->mobile_number) }}" 
                                           pattern="[0-9]{10}" required>
                                    @error('mobile_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">Alternate Phone</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" pattern="[0-9]{10}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $customer->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : '') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="text-primary mb-3">Address & Documents</h5>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="aadhar_number">Aadhar Number</label>
                                    <input type="text" class="form-control @error('aadhar_number') is-invalid @enderror" 
                                           id="aadhar_number" name="aadhar_number" value="{{ old('aadhar_number', $customer->aadhar_number) }}" 
                                           pattern="[0-9]{12}" maxlength="12">
                                    @error('aadhar_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="pan_number">PAN Number</label>
                                    <input type="text" class="form-control @error('pan_number') is-invalid @enderror" 
                                           id="pan_number" name="pan_number" value="{{ old('pan_number', $customer->pan_number) }}" 
                                           pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10" style="text-transform: uppercase;">
                                    @error('pan_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Savings Account Information -->
                                <h6 class="text-info mt-4 mb-3">Savings Account Details</h6>
                                
                                <div class="form-group">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="has_savings_account" 
                                               name="has_savings_account" value="1" 
                                               {{ old('has_savings_account', $customer->has_savings_account) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="has_savings_account">
                                            Customer has savings account
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group" id="savings_account_section">
                                    <label for="savings_account_no">Savings Account Number</label>
                                    <input type="text" class="form-control @error('savings_account_no') is-invalid @enderror" 
                                           id="savings_account_no" name="savings_account_no" 
                                           value="{{ old('savings_account_no', $customer->savings_account_no) }}">
                                    @error('savings_account_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Customer
                        </button>
                        <a href="{{ route('agent.customers.show', $customer) }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.required::after {
    content: " *";
    color: red;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const hasAccountCheckbox = document.getElementById('has_savings_account');
    const accountSection = document.getElementById('savings_account_section');
    
    function toggleAccountSection() {
        if (hasAccountCheckbox.checked) {
            accountSection.style.display = 'block';
        } else {
            accountSection.style.display = 'none';
            document.getElementById('savings_account_no').value = '';
        }
    }
    
    hasAccountCheckbox.addEventListener('change', toggleAccountSection);
    
    // Initialize on page load
    toggleAccountSection();
    
    // Format PAN number to uppercase
    document.getElementById('pan_number').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
});
</script>
@endsection
