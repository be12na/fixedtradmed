<nav class="navbar navbar-expand navbar-light navbar-theme border-bottom" style="--bd-bottom-color: var(--bs-gray-300);">
    <div class="container-fluid px-3">
        <button type="button" class="sidebar-toggler"
            style="--sdbar-toggler-content:'\f5c6'; --sdbar-toggler-content-toggled:'\f5c5'; --sdbar-toggler-color: var(--bs-gray-500);
        --sdbar-toggler-hover-color: var(--bs-gray-600);
        --sdbar-toggler-active-color: var(--bs-gray-800); font-size:24px; margin-left:-0.25rem;">
            <i class="bi"></i>
        </button>
        <ul class="navbar-nav ms-auto">
            @hasPermission(['main.settings.admin.index', 'main.settings.roles.index'])
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link @menuActive(['main.settings.admin', 'main.settings.roles'])" data-bs-toggle="dropdown"
                        data-bs-target="#navbar-role" title="Administrator dan Akses">
                        <i class="fa-solid fa-user-lock"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pt-0 pb-1 rounded-3 bg-light" id="navbar-role">
                        <div class="dropdown-header rounded-top">Administrator dan Akses</div>
                        @hasPermission('main.settings.admin.index')
                            <a href="{{ route('main.settings.admin.index') }}"
                                class="dropdown-item @menuActive('main.settings.admin')">Administrator</a>
                        @endhasPermission

                        @hasPermission('main.settings.roles.index')
                            <a href="{{ route('main.settings.roles.index') }}" class="dropdown-item @menuActive('main.settings.roles')">Hak
                                Akses</a>
                        @endhasPermission
                    </div>
                </li>
            @endhasPermission

            @hasPermission([
                'main.settings.bank.index',
                'main.settings.bonus.index',
                'main.settings.mitra.index',
                'main.settings.reward.index',
                'main.settings.quota.index'
            ])
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link @menuActive(['main.settings.bank', 'main.settings.bonus', 'main.settings.mitra', 'main.settings.reward', 'main.settings.quota'])" data-bs-toggle="dropdown"
                        data-bs-target="#navbar-setting" title="Pengaturan">
                        <i class="fa-solid fa-gear"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end pt-0 pb-1 rounded-3 bg-light" id="navbar-setting">
                        <div class="dropdown-header rounded-top">Pengaturan</div>
                        @hasPermission('main.settings.bank.index')
                            <a href="{{ route('main.settings.bank.index') }}" class="dropdown-item @menuActive('main.settings.bank')">Bank
                                Perusahaan</a>
                        @endhasPermission

                        @hasPermission('main.settings.bonus.index')
                            <a href="{{ route('main.settings.bonus.index') }}" class="dropdown-item @menuActive('main.settings.bonus')">Bonus</a>
                        @endhasPermission

                        @hasPermission('main.settings.mitra.index')
                            <a href="{{ route('main.settings.mitra.index') }}"
                                class="dropdown-item @menuActive('main.settings.mitra')">Member</a>
                        @endhasPermission

                        @hasPermission('main.settings.reward.index')
                            <a href="{{ route('main.settings.reward.index') }}"
                                class="dropdown-item @menuActive('main.settings.reward')">Reward</a>
                        @endhasPermission

                        @hasPermission('main.settings.quota.index')
                            <a href="{{ route('main.settings.quota.index') }}" class="dropdown-item @menuActive('main.settings.quota')">
                                Kuota Belanja
                            </a>
                        @endhasPermission
                    </div>
                </li>
            @endhasPermission

            <li class="nav-item dropdown ms-3">
                <a href="#"
                    class="nav-link d-flex align-items-center justify-content-center py-0 border rounded-circle @menuActive(['password.index'])"
                    data-bs-toggle="dropdown" data-bs-target="#navbar-auth" style="width:36px; height:36px;"
                    title="{{ Auth::user()->name }}">
                    <i class="fs-4 p-0 fa-solid fa-user p-1"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end pt-0 pb-1 rounded-3 bg-light" id="navbar-auth">
                    <div class="dropdown-header rounded-top">{{ Auth::user()->name }}</div>
                    <a href="{{ route('password.index') }}" class="dropdown-item @menuActive('password.index')">Password</a>
                    <div class="dropdown-divider my-1"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item">Logout</a>
                </div>
            </li>
        </ul>
    </div>
</nav>
