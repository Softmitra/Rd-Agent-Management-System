<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <!-- <img src="{{ asset('images/logo.png') }}" alt="RD Agent Logo" class="brand-image img-circle elevation-3" style="opacity: .8"> -->
        <span class="brand-text font-weight-light">RD Agent</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                @if(Auth::user()->hasRole('admin'))
                <li class="nav-item">
                    <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>All Customers</p>
                    </a>
                </li>
                @else
                <li class="nav-item">
                    <a href="{{ route('agent.customers.index') }}" class="nav-link {{ request()->routeIs('agent.customers.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>My Customers</p>
                    </a>
                </li>
                @endif
                
                @if(auth()->user()->id == 1)
                <li class="nav-item">
                    <a href="{{ route('admin.rd-accounts.index') }}" class="nav-link {{ request()->routeIs('admin.rd-accounts.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-piggy-bank"></i>
                        <p>RD Accounts</p>
                    </a>
                </li>
                @else
                <li class="nav-item">
                    <a href="{{ route('agent.rd-agent-accounts.index') }}" class="nav-link {{ request()->routeIs('agent.rd-agent-accounts.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-piggy-bank"></i>
                        <p>RD Agent Accounts</p>
                    </a>
                </li>
                @endif
                
                <li class="nav-item">
                    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-money-bill-wave"></i>
                        <p>Payments</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('collections.index') }}" class="nav-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-coins"></i>
                        <p>Collection Entry</p>
                    </a>
                </li>
                
                <!-- Lot Management -->
                @if(auth()->user()->id == 1)
                <li class="nav-item">
                    <a href="{{ route('admin.lots.index') }}" class="nav-link {{ request()->routeIs('admin.lots.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Lot Management</p>
                    </a>
                </li>
                @else
                <li class="nav-item">
                    <a href="{{ route('agent.lots.index') }}" class="nav-link {{ request()->routeIs('agent.lots.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Lot Management</p>
                    </a>
                </li>
                @endif
                
                <!-- Excel Import for all users -->
                @if(Auth::user()->hasRole('admin'))
                <li class="nav-item">
                    <a href="{{ route('admin.excel-import.index') }}" class="nav-link {{ request()->routeIs('admin.excel-import.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sync-alt text-success"></i>
                        <p>Excel Import</p>
                        <span class="badge badge-success right">Sync</span>
                    </a>
                </li>
                @else
                <li class="nav-item">
                    <a href="{{ route('agent.excel-import.index') }}" class="nav-link {{ request()->routeIs('agent.excel-import.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-sync-alt text-success"></i>
                        <p>Excel Import</p>
                        <span class="badge badge-success right">Sync</span>
                    </a>
                </li>
                @endif
                
                @if(Auth::user()->hasRole('admin'))
                <li class="nav-header">ADMINISTRATION</li>
                
                <li class="nav-item">
                    <a href="{{ route('agents.index') }}" class="nav-link {{ request()->routeIs('agents.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-tie"></i>
                        <p>Agents</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-shield"></i>
                        <p>Roles</p>
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
