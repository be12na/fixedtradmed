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

        @hasPermission(['member.team.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.team'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-team">
                <i class="sidebar-icon bi bi-people"></i>
                <span class="sidebar-text">Anggota</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['member.team'])" id="sb-sub-team" data-bs-parent="#sidebarmenu">
                <a href="{{ route('member.team.index') }}" class="sidebar-item @menuActive('member.team')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Daftar</span>
                </a>
            </div>
        </div>
        @endhasPermission

        @hasPermission(['member.directMitra.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.directMitra'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-mitra">
                <i class="sidebar-icon bi bi-person-workspace"></i>
                <span class="sidebar-text">Member</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['member.directMitra'])" id="sb-sub-mitra" data-bs-parent="#sidebarmenu">
                <a href="{{ route('member.directMitra.index') }}" class="sidebar-item @menuActive('member.directMitra.index')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Daftar</span>
                </a>
                <a href="{{ route('member.directMitra.histories.index') }}" class="sidebar-item @menuActive('member.directMitra.histories.index')">
                    <i class="sidebar-icon bi"></i>
                    <span class="sidebar-text">Riwayat Belanja</span>
                </a>
            </div>
        </div>
        @endhasPermission

        @hasPermission(['member.product.index'])
            <div class="sidebar-dropdown">
                <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.product'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-product">
                    <i class="sidebar-icon bi bi-boxes"></i>
                    <span class="sidebar-text">Produk</span>
                </a>
                <div class="sidebar-submenu collapse @sidebarShow(['member.product'])" id="sb-sub-product" data-bs-parent="#sidebarmenu">
                    @hasPermission('member.product.index')
                        <a href="{{ route('member.product.index') }}" class="sidebar-item @menuActive('member.product.index')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Daftar</span>
                        </a>
                        @if (canStockOpname())
                            <a href="{{ route('member.product.stock.index') }}" class="sidebar-item @menuActive('member.product.stock')">
                                <i class="sidebar-icon bi"></i>
                                <span class="sidebar-text">Stock Opname</span>
                            </a>
                        @endif
                    @endhasPermission
                </div>
            </div>
        @endhasPermission

        @hasPermission(['member.payment.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.payment'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-payment">
                <i class="sidebar-icon bi bi-wallet"></i>
                <span class="sidebar-text">Pembayaran</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['member.payment'])" id="sb-sub-payment" data-bs-parent="#sidebarmenu">
                @hasPermission('member.payment.index')
                    <a href="{{ route('member.payment.index') }}" class="sidebar-item @menuActive(['member.payment.index', 'member.payment.edit'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                    @hasPermission('member.payment.create')
                        <a href="{{ route('member.payment.create') }}" class="sidebar-item @menuActive('member.payment.create')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Tambah</span>
                        </a>
                    @endhasPermission
                @endhasPermission
            </div>
        </div>
        @endhasPermission

        @hasPermission(['member.sale.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.sale'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-sale">
                <i class="sidebar-icon bi bi-cash-coin"></i>
                <span class="sidebar-text">Penjualan</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['member.sale'])" id="sb-sub-sale" data-bs-parent="#sidebarmenu">
                @hasPermission('member.sale.index')
                    <a href="{{ route('member.sale.index') }}" class="sidebar-item @menuActive(['member.sale.index', 'member.sale.edit'])">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                    @canSale()
                        <a href="{{ route('member.sale.create') }}" class="sidebar-item @menuActive('member.sale.create')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Tambah</span>
                        </a>
                    @endcanSale
                @endhasPermission
            </div>
        </div>
        @endhasPermission

        @hasPermission(['member.transfer.index'])
        <div class="sidebar-dropdown">
            <a href="#" class="sidebar-item @sidebarActiveOrCollapsed(['member.transfer'])" data-bs-toggle="collapse" data-bs-target="#sb-sub-transfer">
                <i class="sidebar-icon bi bi-cash-stack"></i>
                <span class="sidebar-text">Transfer</span>
            </a>
            <div class="sidebar-submenu collapse @sidebarShow(['member.transfer'])" id="sb-sub-transfer" data-bs-parent="#sidebarmenu">
                @hasPermission('member.transfer.index')
                    <a href="{{ route('member.transfer.index') }}" class="sidebar-item @menuActive('member.transfer.index')">
                        <i class="sidebar-icon bi"></i>
                        <span class="sidebar-text">Daftar</span>
                    </a>
                    @hasPermission('member.transfer.create')
                        <a href="{{ route('member.transfer.create') }}" class="sidebar-item @menuActive('member.transfer.create')">
                            <i class="sidebar-icon bi"></i>
                            <span class="sidebar-text">Tambah</span>
                        </a>
                    @endhasPermission
                @endhasPermission
            </div>
        </div>
        @endhasPermission
    </div>
    <div class="d-flex justify-content-end align-items-center px-1 py-1 border-top d-xl-none" style="--bd-top-color: var(--sdbar-bd-color);">
        <button type="button" class="sidebar-toggler" style="--sdbar-toggler-content:'\f5c6'; --sdbar-toggler-content-toggled:'\f5c5'; font-size:24px;">
            <i class="bi"></i>
        </button>
    </div>
</aside>