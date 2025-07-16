<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RD Agent System</title>
    
    <!-- Google Font: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            background: linear-gradient(135deg, #f4f6f9 0%, #e9ecef 100%);
        }
        .register-box {
            width: 500px;
            margin-top: 2rem;
        }
        .register-logo {
            margin-bottom: 2rem;
        }
        .register-logo b {
            font-weight: 600;
            color: #2c3e50;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 2rem;
        }
        .login-box-msg {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
        }
        .input-group {
            margin-bottom: 1.2rem;
        }
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #ced4da;
        }
        .form-control {
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            transform: translateY(-1px);
        }
        .invalid-feedback {
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }
        .custom-file-label {
            padding: 0.75rem 1rem;
        }
        .custom-file-input:focus ~ .custom-file-label {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        }
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        .form-section:last-child {
            border-bottom: none;
        }
        .section-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        .login-link {
            color: #007bff;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .login-link:hover {
            color: #0056b3;
            text-decoration: none;
        }
    </style>
</head>
<body class="hold-transition register-page">
    <div class="register-box">
        <div class="register-logo text-center">
            <a href="{{ url('/') }}"><b>RD</b> Agent</a>
        </div>
        
        <div class="card">
            <div class="card-body register-card-body">
                <p class="login-box-msg text-center">Create a New Agent Account</p>
                
                <form action="{{ route('register') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-section">
                        <h5 class="section-title">Personal Information</h5>
                        <div class="input-group">
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                placeholder="Full name" value="{{ old('name') }}" required autofocus>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-user"></span>
                                </div>
                            </div>
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="input-group">
                            <input type="text" name="agent_id" class="form-control @error('agent_id') is-invalid @enderror" 
                                placeholder="Agent ID" value="{{ old('agent_id') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-id-badge"></span>
                                </div>
                            </div>
                            @error('agent_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="input-group">
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                placeholder="Email" value="{{ old('email') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            @error('email')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="input-group">
                            <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" 
                                placeholder="Mobile Number" value="{{ old('mobile_number') }}" 
                                pattern="[0-9]{10}" maxlength="10" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-phone"></span>
                                </div>
                            </div>
                            @error('mobile_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title">Security</h5>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            @error('password')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="input-group">
                            <input type="password" name="password_confirmation" class="form-control" 
                                placeholder="Confirm password" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title">Identity Documents</h5>
                        <div class="input-group">
                            <input type="text" name="aadhar_number" class="form-control @error('aadhar_number') is-invalid @enderror" 
                                placeholder="Aadhar Number" value="{{ old('aadhar_number') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-id-card"></span>
                                </div>
                            </div>
                            @error('aadhar_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="input-group">
                            <input type="text" name="pan_number" class="form-control @error('pan_number') is-invalid @enderror" 
                                placeholder="PAN Number" value="{{ old('pan_number') }}" required>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-id-card"></span>
                                </div>
                            </div>
                            @error('pan_number')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('aadhar_image') is-invalid @enderror" 
                                    id="aadhar_image" name="aadhar_image" accept="image/*">
                                <label class="custom-file-label" for="aadhar_image">Aadhar Card Image (Optional)</label>
                            </div>
                            @error('aadhar_image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="input-group">
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('pan_image') is-invalid @enderror" 
                                    id="pan_image" name="pan_image" accept="image/*">
                                <label class="custom-file-label" for="pan_image">PAN Card Image (Optional)</label>
                            </div>
                            @error('pan_image')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section">
                        <h5 class="section-title">Additional Information</h5>
                        <div class="input-group">
                            <input type="text" name="branch" class="form-control @error('branch') is-invalid @enderror" 
                                placeholder="Branch (optional)" value="{{ old('branch') }}">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-building"></span>
                                </div>
                            </div>
                            @error('branch')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="input-group">
                            <textarea name="contact_info[address]" class="form-control @error('contact_info.address') is-invalid @enderror" 
                                placeholder="Address (optional)" rows="2">{{ old('contact_info.address') }}</textarea>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-map-marker-alt"></span>
                                </div>
                            </div>
                            @error('contact_info.address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-8">
                            <a href="{{ route('login') }}" class="login-link">Already have an account?</a>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-primary btn-block">Register</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    <script>
        // Update custom file input labels
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    </script>
</body>
</html> 