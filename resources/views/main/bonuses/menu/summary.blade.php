<div class="d-flex flex-nowrap align-items-end justify-content-between dropdown border-bottom mb-2 pb-1">
    <span class="text-nowrap me-3" id="summary-menu-title">{{ $bonusTitle }}</span>
    <button class="btn btn-sm btn-success" data-bs-toggle="dropdown" data-bs-target="#bonusMenu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div class="dropdown-menu pt-0 pb-1" id="bonusMenu">
        <div class="dropdown-header rounded-top">Menu</div>
        <a class="dropdown-item @if($activeMenu == 'totalSummary') active @endif" href="{{ route('main.bonus.summary.indexSummary', ['summaryName' => 'total']) }}">Total Summary</a>
        <div class="dropdown-divider my-1"></div>
        {{-- <a class="dropdown-item @if($activeMenu == 'royaltySummary') active @endif" href="{{ route('main.bonus.summary.indexSummary', ['summaryName' => 'royalty']) }}">Bonus Royalty</a>
        <a class="dropdown-item @if($activeMenu == 'overrideSummary') active @endif" href="{{ route('main.bonus.summary.indexSummary', ['summaryName' => 'override']) }}">Bonus Override</a> --}}
        <a class="dropdown-item @if($activeMenu == 'teamSummary') active @endif" href="{{ route('main.bonus.summary.indexSummary', ['summaryName' => 'team']) }}">Bonus Team</a>
        <a class="dropdown-item @if($activeMenu == 'saleSummary') active @endif" href="{{ route('main.bonus.summary.indexSummary', ['summaryName' => 'sale']) }}">Bonus Penjualan</a>
    </div>
</div>