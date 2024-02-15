@php
    $authUser = auth()->user();
    $hasPackage = $authUser->has_package;
@endphp
<div class="sidebar-overlay"></div>
<aside class="sidebar">
    <a href="{{ route('welcome') }}" class="sidebar-brand">
        <img alt="{{ config('app.name') }}" src="{{ asset('images/logo-main.png') }}">
    </a>
    <div class="sidebar-menu" id="sidebarmenu">
        <a href="{{ route('dashboard') }}" class="sidebar-item @menuActive(['dashboard'])">
            <i class="sidebar-icon bi bi-activity"></i>
            <span class="sidebar-text">Dashboard</span>
        </a>

        {{-- @if ($authUser->show_package_menu)
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.package.index', 'mitra.package.history', 'mitra.package.ro.transfer'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-package">
                    <i class="sidebar-icon bi bi-stack"></i>
                    <span class="sidebar-text">Paket</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.package.index', 'mitra.package.history', 'mitra.package.ro.transfer'])" id="sb-sub-package" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('mitra.package.history') }}" class="sidebar-item @menuActive(['mitra.package.index', 'mitra.package.history', 'mitra.package.ro.transfer'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">History</span>
                    </a>
                </div>
            </div>
        @endif --}}

        @hasPermission(['mitra.myProducts.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.myProducts'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-product">
                    <i class="sidebar-icon bi bi-boxes"></i>
                    <span class="sidebar-text">Produk</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.myProducts'])" id="sb-sub-product" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('mitra.myProducts.index') }}" class="sidebar-item @menuActive('mitra.myProducts.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                </div>
            </div>
        @endhasPermission

        @hasPermission(['mitra.myMember.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.myMember'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-mitra">
                    <i class="sidebar-icon bi bi-person-workspace"></i>
                    <span class="sidebar-text">Anggota</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.myMember'])" id="sb-sub-mitra" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('mitra.myMember.index') }}" class="sidebar-item @menuActive('mitra.myMember.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                    <a href="{{ route('mitra.myMember.histories.index') }}" class="sidebar-item @menuActive('mitra.myMember.histories.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Riwayat Belanja</span>
                    </a>
                </div>
            </div>
        @endhasPermission

        @hasPermission(['mitra.purchase.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.purchase'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-sale">
                    <i class="sidebar-icon bi bi-cart-plus-fill"></i>
                    <span class="sidebar-text">Pembelian</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.purchase'])" id="sb-sub-sale" data-bs-parent="#sidebarmenu">
                    @hasPermission('mitra.purchase.index')
                        <a href="{{ route('mitra.purchase.index') }}" class="sidebar-item @menuActive(['mitra.purchase.index', 'mitra.purchase.edit', 'mitra.purchase.transfer'])">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Daftar</span>
                        </a>
                        @hasPermission('mitra.purchase.create')
                            <a href="{{ route('mitra.purchase.create') }}" class="sidebar-item @menuActive('mitra.purchase.create')">
                                <i class="sidebar-icon bi"></i>
                                <span class="sidebar-text">Tambah</span>
                            </a>
                        @endhasPermission
                    @endhasPermission

                </div>
            </div>
        @endhasPermission

        @hasPermission(['mitra.point.my-shopping.index', 'mitra.point.activate-member.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.point'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-point">
                    <i class="sidebar-icon bi bi-percent"></i>
                    <span class="sidebar-text">Poin</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.point'])" id="sb-sub-point" data-bs-parent="#sidebarmenu">
                    {{-- @hasPermission('mitra.point.activate-member.index')
                        <a href="{{ route('mitra.point.activate-member.index') }}" class="sidebar-item @menuActive('mitra.point.activate-member')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Aktifasi Member</span>
                        </a>
                    @endhasPermission --}}

                    {{-- @hasPermission('mitra.point.my-shopping.index')
                        <a href="{{ route('mitra.point.my-shopping.index') }}" class="sidebar-item @menuActive('mitra.point.my-shopping')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Belanja Pribadi</span>
                        </a>
                    @endhasPermission --}}

                    @hasPermission('mitra.point.reward.index')
                        <a href="{{ route('mitra.point.reward.index') }}" class="sidebar-item @menuActive('mitra.point.reward.index')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Reward</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission([
            'mitra.bonus.sponsor.index',
            'mitra.bonus.sponsor-ro.index',
            'mitra.bonus.cashback.index',
            'mitra.bonus.generasi.index',
            'mitra.bonus.prestasi.index',
            'mitra.bonus.point-ro.index'
        ])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['mitra.bonus'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-bonus">
                    <i class="sidebar-icon bi bi-coin"></i>
                    <span class="sidebar-text">Bonus</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['mitra.bonus'])" id="sb-sub-bonus" data-bs-parent="#sidebarmenu">
                    @hasPermission('mitra.bonus.sponsor.index')
                        <a href="{{ route('mitra.bonus.sponsor.index') }}" class="sidebar-item @menuActive('mitra.bonus.sponsor')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Sponsor</span>
                        </a>
                    @endhasPermission

                    @hasPermission('mitra.bonus.sponsor-ro.index')
                        <a href="{{ route('mitra.bonus.sponsor-ro.index') }}" class="sidebar-item @menuActive('mitra.bonus.sponsor-ro')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Sponsor RO</span>
                        </a>
                    @endhasPermission

                    @hasPermission('mitra.bonus.cashback.index')
                        <a href="{{ route('mitra.bonus.cashback.index') }}" class="sidebar-item @menuActive('mitra.bonus.cashback')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Cashback</span>
                        </a>
                    @endhasPermission

                    

                    @hasPermission('mitra.bonus.generasi.index')
                        <a href="{{ route('mitra.bonus.generasi.index') }}" class="sidebar-item @menuActive('mitra.bonus.generasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Generasi</span>
                        </a>
                    @endhasPermission

                    @hasPermission('mitra.bonus.prestasi.index')
                        <a href="{{ route('mitra.bonus.prestasi.index') }}" class="sidebar-item @menuActive('mitra.bonus.prestasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Prestasi</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission
    </div>
    <div class="d-flex justify-content-end align-items-center px-1 py-1 border-top d-xl-none"
        style="--bd-top-color: var(--sdbar-bd-color);">
        <button type="button" class="sidebar-toggler"
            style="--sdbar-toggler-content:'\f5c6'; --sdbar-toggler-content-toggled:'\f5c5'; font-size:24px;">
            <i class="bi"></i>
        </button>
    </div>
</aside>
