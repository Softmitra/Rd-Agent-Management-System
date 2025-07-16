<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications -->
        @if(Auth::user()->hasRole('admin'))
            <li class="nav-item">
                @include('components.notification-bell')
            </li>
        @endif
        
        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle"></i>
                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'Super Admin' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <i class="fas fa-user-circle fa-3x"></i>
                    <p>
                        {{ Auth::user()->name ?? 'Super Admin' }}
                        <small>Member since {{ Auth::user()->created_at ? Auth::user()->created_at->format('M. Y') : 'N/A' }}</small>
                    </p>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <a href="{{ route('profile.edit') }}" class="btn btn-default btn-flat">Profile</a>

                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-default btn-flat float-right">Sign out</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav> 