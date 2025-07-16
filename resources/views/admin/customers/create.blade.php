@extends('layouts.app')

@section('title', 'Create Customer')

@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h5>Create Customer</h5>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('customers.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

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
            <div class="col-md-12">
                <div class="card">
                    <!-- <div class="card-header">
                        <h3 class="card-title">Customer Information</h3>
                    </div> -->
                    <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <!-- Basic Information -->

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="Enter Name" autocomplete="off" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email') }}"
                                            placeholder="Enter Email" autocomplete="off" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mobile_number">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('mobile_number') is-invalid @enderror"
                                            id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}"
                                            placeholder="Enter Mobile Number" pattern="[0-9]{10}"
                                            title="Please enter a valid 10-digit mobile number" autocomplete="off" required>
                                        @error('mobile_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone') }}"
                                            placeholder="Enter Phone Number" autocomplete="off" pattern="[0-9]{10}"
                                            title="Please enter a valid 10-digit phone number" required>
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="agent_id">Select Agent <span class="text-danger">*</span></label>
                                        <select class="form-control select2 @error('agent_id') is-invalid @enderror"
                                            id="agent_id" name="agent_id" autocomplete="off" required>
                                            <option value="">Select Agent</option>
                                            @foreach ($agents as $agent)
                                                @if ($agent->id != auth()->id())
                                                    <option value="{{ $agent->id }}"
                                                        data-phone="{{ $agent->mobile_number }}"
                                                        {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                                        {{ $agent->name }} - {{ $agent->mobile_number }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('agent_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="cif_id">CIF ID</label>
                                        <input type="text" class="form-control @error('cif_id') is-invalid @enderror"
                                            id="cif_id" name="cif_id" value="{{ old('cif_id') }}"
                                            placeholder="Enter CIF ID" autocomplete="off">
                                        @error('cif_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('date_of_birth') is-invalid @enderror"
                                            id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}"
                                            autocomplete="off" required>
                                        @error('date_of_birth')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="address">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
                                            placeholder="Enter Address" autocomplete="off" required>{{ old('address') }}</textarea>
                                        @error('address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="photo">Customer Photo</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file"
                                                    class="custom-file-input @error('photo') is-invalid @enderror"
                                                    id="photo" name="photo" accept=".jpg,.jpeg,.png"
                                                    autocomplete="off">
                                                <label class="custom-file-label" for="photo">Choose file</label>
                                            </div>
                                        </div>
                                        <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG. Max size:
                                            2MB</small>
                                        @error('photo')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Create</button>
                            <a href="{{ route('customers.index') }}" class="btn btn-default float-right">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            $(function() {
                // Update custom file input labels
                $('.custom-file-input').on('change', function() {
                    let fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').addClass("selected").html(fileName);
                });

                // Format Aadhar number as it's typed
                $('#aadhar_number').on('input', function() {
                    let value = $(this).val().replace(/\D/g, '').substring(0, 12);
                    $(this).val(value);
                });

                // Format PAN number as it's typed (uppercase and pattern)
                $('#pan_number').on('input', function() {
                    let value = $(this).val().toUpperCase();
                    value = value.replace(/[^A-Z0-9]/g, '').substring(0, 10);
                    $(this).val(value);
                });

                // Format mobile number as it's typed
                $('#mobile_number').on('input', function() {
                    let value = $(this).val().replace(/\D/g, '').substring(0, 10);
                    $(this).val(value);
                });

                // Toggle savings account fields
                function toggleSavingsAccountFields() {
                    if ($('#has_savings_account').is(':checked')) {
                        $('.savings-account-fields').slideDown();
                    } else {
                        $('.savings-account-fields').slideUp();
                        $('#savings_account_no').val('');
                    }
                }

                // Initial state
                toggleSavingsAccountFields();

                // Handle toggle change
                $('#has_savings_account').on('change', toggleSavingsAccountFields);

                // Format CIF ID as it's typed (remove spaces and special characters)
                $('#cif_id').on('input', function() {
                    let value = $(this).val().replace(/[^A-Z0-9]/g, '').substring(0, 50);
                    $(this).val(value);
                });

                // Format Savings Account Number as it's typed (remove spaces and special characters)
                $('#savings_account_no').on('input', function() {
                    let value = $(this).val().replace(/[^0-9]/g, '').substring(0, 50);
                    $(this).val(value);
                });
            });
        </script>
    @endpush
@endsection
