@extends('layouts.app')

@section('title', 'Create Agent')

@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Create Agent</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('agents.index') }}" class="btn btn-default">
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
                    <div class="card-header">
                        <h3 class="card-title">Agent Information</h3>
                    </div>
                    <form action="{{ route('agents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}" autocomplete="off"
                                            placeholder="Enter full name" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" autocomplete="off" value="{{ old('email') }}"
                                            placeholder="Enter email address" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="mobile_number">Mobile Number</label>
                                        <input type="text"
                                            class="form-control @error('mobile_number') is-invalid @enderror"
                                            id="mobile_number" autocomplete="off" name="mobile_number"
                                            value="{{ old('mobile_number') }}" pattern="[0-9]{10}" maxlength="10"
                                            placeholder="Enter 10-digit mobile number" required>
                                        @error('mobile_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" autocomplete="off" name="password" placeholder="Enter password"
                                            required>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm Password</label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" autocomplete="off" placeholder="Re-enter password"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhar_number">Aadhar Number</label>
                                        <input type="text"
                                            class="form-control @error('aadhar_number') is-invalid @enderror"
                                            id="aadhar_number" name="aadhar_number" autocomplete="off"
                                            value="{{ old('aadhar_number') }}" placeholder="Enter Aadhar number" required>
                                        @error('aadhar_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pan_number">PAN Number</label>
                                        <input type="text" class="form-control @error('pan_number') is-invalid @enderror"
                                            id="pan_number" name="pan_number" autocomplete="off"
                                            value="{{ old('pan_number') }}" placeholder="Enter PAN number" required>
                                        @error('pan_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_info[phone]">Contact Phone</label>
                                        <input type="text"
                                            class="form-control @error('contact_info.phone') is-invalid @enderror"
                                            id="contact_info[phone]" name="contact_info[phone]" autocomplete="off"
                                            value="{{ old('contact_info.phone') }}"
                                            placeholder="Alternate contact number" required>
                                        @error('contact_info.phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="branch">Branch</label>
                                        <input type="text" class="form-control @error('branch') is-invalid @enderror"
                                            id="branch" name="branch" value="{{ old('branch') }}"
                                            autocomplete="off" placeholder="Enter branch name" required>
                                        @error('branch')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_info[address]">Address</label>
                                        <textarea class="form-control @error('contact_info.address') is-invalid @enderror" id="contact_info[address]"
                                            name="contact_info[address]" rows="3" autocomplete="off" placeholder="Enter address" required>{{ old('contact_info.address') }}</textarea>
                                        @error('contact_info.address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhar_file">Aadhar Card</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" autocomplete="off"
                                                    class="custom-file-input @error('aadhar_file') is-invalid @enderror"
                                                    id="aadhar_file" name="aadhar_file" accept=".pdf,.jpg,.jpeg,.png"
                                                    required>
                                                <label class="custom-file-label" for="aadhar_file">Choose Aadhar
                                                    file</label>
                                            </div>
                                        </div>
                                        @error('aadhar_file')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pan_file">PAN Card</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file" autocomplete="off"
                                                    class="custom-file-input @error('pan_file') is-invalid @enderror"
                                                    id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png"
                                                    required>
                                                <label class="custom-file-label" for="pan_file">Choose PAN file</label>
                                            </div>
                                        </div>
                                        @error('pan_file')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Create Agent</button>
                            <a href="{{ route('agents.index') }}" class="btn btn-default float-right">Cancel</a>
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
            });
        </script>
    @endpush
@endsection
