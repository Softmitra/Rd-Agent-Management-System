@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Edit Agent</h1>
            </div>
            <div class="col-sm-6">
                <div class="float-sm-right">
                    <a href="{{ route('agents.show', $agent) }}" class="btn btn-default">
                        <i class="fas fa-arrow-left"></i> Back to Details
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
                    <form action="{{ route('agents.update', $agent) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Name</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $agent->name) }}"
                                            placeholder="Enter full name" autocomplete="off" required>
                                        @error('name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $agent->email) }}"
                                            placeholder="Enter email address" autocomplete="off" required>
                                        @error('email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhar_number">Aadhar Number</label>
                                        <input type="text" maxlength="12"
                                            class="form-control @error('aadhar_number') is-invalid @enderror"
                                            id="aadhar_number" name="aadhar_number"
                                            value="{{ old('aadhar_number', $agent->aadhar_number) }}"
                                            placeholder="Enter Aadhar number" autocomplete="off" required>
                                        @error('aadhar_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="pan_number">PAN Number</label>
                                        <input type="text" class="form-control @error('pan_number') is-invalid @enderror"
                                            id="pan_number" name="pan_number"
                                            value="{{ old('pan_number', $agent->pan_number) }}"
                                            placeholder="Enter PAN number" autocomplete="off"
                                            pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" maxlength="10"
                                            oninput="this.value = this.value.toUpperCase()" required>
                                        @error('pan_number')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Format: 5 letters, 4 digits, 1 letter (e.g., ABCDE1234F)</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_info[phone]">Contact Phone</label>
                                        <input type="text" maxlength="10"
                                            class="form-control @error('contact_info.phone') is-invalid @enderror"
                                            id="contact_info[phone]" name="contact_info[phone]"
                                            value="{{ old('contact_info.phone', $agent->contact_info['phone'] ?? '') }}"
                                            placeholder="Enter contact number" autocomplete="off" required>
                                        @error('contact_info.phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="branch">Branch</label>
                                        <input type="text" class="form-control @error('branch') is-invalid @enderror"
                                            id="branch" name="branch" value="{{ old('branch', $agent->branch) }}"
                                            placeholder="Enter branch name" autocomplete="off" required>
                                        @error('branch')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="contact_info[address]">Address</label>
                                <textarea class="form-control @error('contact_info.address') is-invalid @enderror" id="contact_info[address]"
                                    name="contact_info[address]" rows="3" placeholder="Enter full address" autocomplete="off" required>{{ old('contact_info.address', $agent->contact_info['address'] ?? '') }}</textarea>
                                @error('contact_info.address')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="aadhar_file">Aadhar Card</label>
                                        <div class="input-group">
                                            <div class="custom-file">
                                                <input type="file"
                                                    class="custom-file-input @error('aadhar_file') is-invalid @enderror"
                                                    id="aadhar_file" name="aadhar_file" accept=".pdf,.jpg,.jpeg,.png"
                                                    autocomplete="off">
                                                <label class="custom-file-label" for="aadhar_file">Choose Aadhar
                                                    file</label>
                                            </div>
                                        </div>
                                        @if ($agent->aadhar_file)
                                            <small class="form-text text-muted">
                                                Current file: <a href="{{ Storage::url($agent->aadhar_file) }}"
                                                    target="_blank">View Aadhar Card</a>
                                            </small>
                                        @endif
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
                                                <input type="file"
                                                    class="custom-file-input @error('pan_file') is-invalid @enderror"
                                                    id="pan_file" name="pan_file" accept=".pdf,.jpg,.jpeg,.png"
                                                    autocomplete="off">
                                                <label class="custom-file-label" for="pan_file">Choose PAN file</label>
                                            </div>
                                        </div>
                                        @if ($agent->pan_file)
                                            <small class="form-text text-muted">
                                                Current file: <a href="{{ Storage::url($agent->pan_file) }}"
                                                    target="_blank">View PAN Card</a>
                                            </small>
                                        @endif
                                        @error('pan_file')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Update Agent</button>
                            <a href="{{ route('agents.show', $agent) }}" class="btn btn-default float-right">Cancel</a>
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
