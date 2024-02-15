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

        @hasPermission(['main.master.product-category.index', 'main.master.product.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.master'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-produk">
                    <i class="sidebar-icon bi bi-boxes"></i>
                    <span class="sidebar-text">Master Data</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.master'])" id="sb-sub-produk" data-bs-parent="#sidebarmenu">
                    @hasPermission('main.master.product-category.index')
                        <a href="{{ route('main.master.product-category.index') }}" class="sidebar-item @menuActive('main.master.product-category')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Kategori Produk</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.master.product.index')
                        <a href="{{ route('main.master.product.index') }}" class="sidebar-item @menuActive('main.master.product')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Produk</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.branch.list.index', 'main.branch.product.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.branch'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-branch">
                    <i class="sidebar-icon bi bi-hdd-network"></i>
                    <span class="sidebar-text">Kantor Cabang</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.branch'])" id="sb-sub-branch" data-bs-parent="#sidebarmenu">
                    @hasPermission('main.branch.list.index')
                        <a href="{{ route('main.branch.list.index') }}" class="sidebar-item @menuActive('main.branch.list')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Daftar</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.branch.product.index')
                        <a href="{{ route('main.branch.product.index') }}" class="sidebar-item @menuActive('main.branch.product')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Produk</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.member.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.member'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-members">
                    <i class="sidebar-icon bi bi-people"></i>
                    <span class="sidebar-text">Anggota</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.member'])" id="sb-sub-members" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('main.member.index') }}" class="sidebar-item @menuActive('main.member.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>

                    @hasPermission('main.member.structure.basic')
                        <a href="{{ route('main.member.structure.basic') }}" class="sidebar-item @menuActive('main.member.structure.basic')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Struktur Dasar</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.member.structure.table')
                        <a href="{{ route('main.member.structure.table') }}" class="sidebar-item @menuActive('main.member.structure.table')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Struktur Table</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.member.structure.tree')
                        <a href="{{ route('main.member.structure.tree') }}" class="sidebar-item @menuActive('main.member.structure.tree')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Struktur Diagram</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.mitra.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.mitra'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-mitra">
                    <i class="sidebar-icon bi bi-person-workspace"></i>
                    <span class="sidebar-text">Member</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.mitra'])" id="sb-sub-mitra" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('main.mitra.index') }}" class="sidebar-item @menuActive(['main.mitra.index', 'main.mitra.products.index'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>

                    {{-- @hasPermission('main.mitra.register.index')
                <a href="{{ route('main.mitra.register.index') }}" class="sidebar-item @menuActive(['main.mitra.register'])">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Member Baru</span>
                </a>
                @endhasPermission --}}
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.payments.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.payments'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-payment">
                    <i class="sidebar-icon bi bi-wallet"></i>
                    <span class="sidebar-text">Pembayaran</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.payments'])" id="sb-sub-payment" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('main.payments.index') }}" class="sidebar-item @menuActive(['main.payments.index', 'main.payments.edit'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Setoran</span>
                    </a>
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.sales.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.sales'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-sales">
                    <i class="sidebar-icon bi bi-stack"></i>
                    <span class="sidebar-text">Penjualan</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.sales'])" id="sb-sub-sales" data-bs-parent="#sidebarmenu">
                    <a href="{{ route('main.sales.index') }}" class="sidebar-item @menuActive(['main.sales.index', 'main.sales.edit'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.transfers.sales.index', 'main.transfers.mitra.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.transfers'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-transfers">
                    <i class="sidebar-icon bi bi-cash-stack"></i>
                    <span class="sidebar-text">Transfer</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.transfers'])" id="sb-sub-transfers"
                    data-bs-parent="#sidebarmenu">
                    @hasPermission('main.transfers.sales.index')
                        <a href="{{ route('main.transfers.sales.index') }}" class="sidebar-item @menuActive('main.transfers.sales')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Penjualan</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.transfers.mitra.index')
                        <a href="{{ route('main.transfers.mitra.index') }}" class="sidebar-item @menuActive('main.transfers.mitra')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Member</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission([
            'main.memberBonus.sponsor.index',
            'main.memberBonus.sponsor-ro.index',
            'main.memberBonus.cashback.index',
            'main.memberBonus.point-ro.index',
            'main.memberBonus.generasi.index',
            'main.memberBonus.prestasi.index'
        ])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.memberBonus'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-bonus">
                    <i class="sidebar-icon bi bi-percent"></i>
                    <span class="sidebar-text">Bonus</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.memberBonus'])" id="sb-sub-bonus" data-bs-parent="#sidebarmenu">
                    {{-- @hasPermission('main.bonus.point.self.index')
                <a href="{{ route('main.bonus.point.self.index') }}" class="sidebar-item @menuActive('main.bonus.point.self')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Poin Pribadi</span>
                </a>
                @endhasPermission --}}

                    {{-- @hasPermission('main.bonus.point.upline.index')
                <a href="{{ route('main.bonus.point.upline.index') }}" class="sidebar-item @menuActive('main.bonus.point.upline')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Poin Upline</span>
                </a>
                @endhasPermission --}}
                    @hasPermission('main.memberBonus.sponsor.index')
                        <a href="{{ route('main.memberBonus.sponsor.index') }}" class="sidebar-item @menuActive('main.memberBonus.sponsor')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Sponsor</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.memberBonus.sponsor-ro.index')
                        <a href="{{ route('main.memberBonus.sponsor-ro.index') }}" class="sidebar-item @menuActive('main.memberBonus.sponsor-ro')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Sponsor RO</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.memberBonus.cashback.index')
                        <a href="{{ route('main.memberBonus.cashback.index') }}" class="sidebar-item @menuActive('main.memberBonus.cashback')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Cashback</span>
                        </a>
                    @endhasPermission

                    

                    @hasPermission('main.memberBonus.generasi.index')
                        <a href="{{ route('main.memberBonus.generasi.index') }}" class="sidebar-item @menuActive('main.memberBonus.generasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Generasi</span>
                        </a>
                    @endhasPermission

                    @hasPermission('main.memberBonus.prestasi.index')
                        <a href="{{ route('main.memberBonus.prestasi.index') }}" class="sidebar-item @menuActive('main.memberBonus.prestasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Prestasi</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission([
            'main.withdraw.sponsor.index',
            'main.withdraw.sponsor-ro.index',
            'main.withdraw.cashback.index',
            'main.withdraw.point-ro.index',
            'main.withdraw.generasi.index',
            'main.withdraw.prestasi.index'
        ])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.withdraw'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-withdraw">
                    <i class="sidebar-icon bi bi-cash-coin"></i>
                    <span class="sidebar-text">Withdraw</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.withdraw'])" id="sb-sub-withdraw"
                    data-bs-parent="#sidebarmenu">
                    @hasPermission('main.withdraw.sponsor.index')
                        <a href="{{ route('main.withdraw.sponsor.index') }}" class="sidebar-item @menuActive('main.withdraw.sponsor')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Bonus Sponsor</span>
                        </a>
                    @endhasPermission
                    @hasPermission('main.withdraw.sponsor-ro.index')
                        <a href="{{ route('main.withdraw.sponsor-ro.index') }}" class="sidebar-item @menuActive('main.withdraw.sponsor-ro')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Bonus Sponsor RO</span>
                        </a>
                    @endhasPermission
                    @hasPermission('main.withdraw.cashback.index')
                        <a href="{{ route('main.withdraw.cashback.index') }}" class="sidebar-item @menuActive('main.withdraw.cashback')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Bonus Cashback</span>
                        </a>
                    @endhasPermission
                    
                    @hasPermission('main.withdraw.generasi.index')
                        <a href="{{ route('main.withdraw.generasi.index') }}" class="sidebar-item @menuActive('main.withdraw.generasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Bonus Generasi</span>
                        </a>
                    @endhasPermission
                    @hasPermission('main.withdraw.prestasi.index')
                        <a href="{{ route('main.withdraw.prestasi.index') }}" class="sidebar-item @menuActive('main.withdraw.prestasi')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Bonus Prestasi</span>
                        </a>
                    @endhasPermission
                    @hasPermission('main.withdraw.histories.index')
                        <a href="{{ route('main.withdraw.histories.index') }}" class="sidebar-item @menuActive('main.withdraw.histories')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Riwayat</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission(['main.point.shopping.index', 'main.point.activate.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.point'])" data-bs-toggle="collapse"
                    data-bs-target="#sb-sub-point">
                    <i class="sidebar-icon bi bi-123"></i>
                    <span class="sidebar-text">Poin</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['main.point'])" id="sb-sub-point" data-bs-parent="#sidebarmenu">
                    {{-- @hasPermission('main.point.activate.index')
                <a href="{{ route('main.point.activate.index') }}" class="sidebar-item @menuActive('main.point.activate')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Aktifasi Member</span>
                </a>
                @endhasPermission --}}

                    {{-- @hasPermission('main.point.shopping.index')
                <a href="{{ route('main.point.shopping.index') }}" class="sidebar-item @menuActive('main.point.shopping')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Belanja Pribadi</span>
                </a>
                @endhasPermission --}}

                    @hasPermission('main.point.claim.index')
                        <a href="{{ route('main.point.claim.index') }}" class="sidebar-item @menuActive('main.point.claim')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Klaim Reward</span>
                        </a>
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        {{-- @hasPermission(['main.reports.global.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['main.reports'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-reports">
                <i class="sidebar-icon bi bi-journals"></i>
                <span class="sidebar-text">Laporan</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['main.reports'])" id="sb-sub-reports" data-bs-parent="#sidebarmenu">
                @hasPermission('main.reports.global.product.index')
                    <a href="{{ route('main.reports.global.product.index') }}" class="sidebar-item @menuActive('main.reports.global.product.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Global Produk</span>
                    </a>
                @endhasPermission

                @hasPermission('main.reports.global.manager.index')
                    <a href="{{ route('main.reports.global.manager.index') }}" class="sidebar-item @menuActive('main.reports.global.manager.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Global Manager</span>
                    </a>
                @endhasPermission

                @hasPermission('main.reports.global.detailManager.index')
                    <a href="{{ route('main.reports.global.detailManager.index') }}" class="sidebar-item @menuActive('main.reports.global.detailManager.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Global Detail Manager</span>
                    </a>
                @endhasPermission

                @hasPermission('main.reports.bonus.distributor.index')
                    <a href="{{ route('main.reports.bonus.distributor.index') }}" class="sidebar-item @menuActive('main.reports.bonus.distributor.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Bonus Distributor</span>
                    </a>
                @endhasPermission
            </div>
        </div>
        @endhasPermission --}}
    </div>
    <div class="d-flex justify-content-end align-items-center px-1 py-1 border-top d-xl-none"
        style="--bd-top-color: var(--sdbar-bd-color);">
        <button type="button" class="sidebar-toggler"
            style="--sdbar-toggler-content:'\f5c6'; --sdbar-toggler-content-toggled:'\f5c5'; font-size:24px;">
            <i class="bi"></i>
        </button>
    </div>
</aside>
