<nav class="navbar navbar-expand navbar-light navbar-theme border-bottom" style="--bd-bottom-color: var(--bs-gray-300);">
    <div class="container-fluid px-3">
        <button type="button" class="sidebar-toggler" style="--sdbar-toggler-content:'\f5c6'; --sdbar-toggler-content-toggled:'\f5c5'; --sdbar-toggler-color: var(--bs-gray-500);
        --sdbar-toggler-hover-color: var(--bs-gray-600);
        --sdbar-toggler-active-color: var(--bs-gray-800); font-size:24px; margin-left:-0.25rem;">
            <i class="bi"></i>
        </button>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a href="#" class="nav-link d-flex align-items-center justify-content-center p-0 border rounded-circle" data-bs-toggle="dropdown" data-bs-target="#auth-menu" style="width:36px; height:36px;">
                    <i class="fs-4 p-0 fa-solid fa-user p-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0 pb-1 rounded-3" id="auth-menu">
                    <div class="dropdown-header rounded-top">{{ Auth::user()->name }}</div>
                    <a href="{{ route('profile.index') }}" class="dropdown-item @menuActive('profile.index')">Profile</a>
                    <a href="{{ route('password.index') }}" class="dropdown-item @menuActive('password.index')">Password</a>
                    <div class="dropdown-divider my-1"></div>
                    <a href="{{ route('mitra.bank.index') }}" class="dropdown-item @menuActive('mitra.bank')">Rekening Bank</a>
                    <div class="dropdown-divider my-1"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>