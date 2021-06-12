<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard.index') }}">
        <div class="sidebar-brand-icon">
            <img src="/images/app-logo.png" class="app-logo" alt="App Logo">
        </div>
        <div class="sidebar-brand-text ml-1 mr-3">eidrun</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ \Route::is('dashboard.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.index') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Nav Item - Payment Wallets -->
    <li class="nav-item {{ \Route::is('payment-wallets.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('payment-wallets.index') }}">
            <i class="fas fa-fw fa-money-bill"></i>
            <span>Payment Wallets</span>
        </a>
    </li>

    <!-- Nav Item - Drop Wallets -->
    <li class="nav-item {{ \Route::is('drop-wallets.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('drop-wallets.index') }}">
            <i class="fas fa-fw fa-random"></i>
            <span>Drop Wallets</span>
        </a>
    </li>

    <!-- Nav Item - Manage Admins -->
    <li class="nav-item {{ \Route::is('manage-admins.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('manage-admins.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Manage Admins</span>
        </a>
    </li>

    <!-- Nav Item - Manage Queue -->
    <li class="nav-item {{ \Route::is('manage-queue.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('manage-queue.index') }}">
            <i class="fas fa-fw fa-list-alt"></i>
            <span>Manage Queue</span>
        </a>
    </li>

    <!-- Nav Item - Settings -->
    <li class="nav-item {{ \Route::is('settings.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('settings.index') }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
