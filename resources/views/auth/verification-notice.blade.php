@extends('layouts.auth')

@section('title', 'Account Verification Required')

@section('content')
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <a href="{{ url('/') }}" class="h1">RD Agent</a>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            <p class="login-box-msg">Your account is pending verification</p>

            <div class="text-center mb-3">
                <i class="fas fa-user-clock fa-4x text-warning mb-3"></i>
            </div>

            <p class="text-center">
                Your account needs to be verified by an administrator before you can access the system. 
                This usually takes 1-2 business days. You will be notified via email once your account is verified.
            </p>

            <div class="row">
                <div class="col-12">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 