<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to RD Agent System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-container {
            max-width: 1200px;
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-body {
            padding: 2.5rem;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
        }
        .admin-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .agent-icon {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        .btn-login {
            padding: 0.8rem 2rem;
            border-radius: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold mb-3">Welcome to RD Agent System</h1>
            <p class="lead text-muted">Please select your login type to continue</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle admin-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h3 class="card-title mb-3">Admin Login</h3>
                        <p class="card-text text-muted mb-4">
                            Access the administrative dashboard to manage users, roles, and system settings.
                        </p>
                        <a href="{{ route('admin.login') }}" class="btn btn-primary btn-login">
                            Login as Admin
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <div class="icon-circle agent-icon">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="card-title mb-3">Agent Login</h3>
                        <p class="card-text text-muted mb-4">
                            Access your agent dashboard to manage your tasks and client interactions.
                        </p>
                        <a href="{{ route('agent.login') }}" class="btn btn-success btn-login">
                            Login as Agent
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
