@php
    $canConfirm = (hasPermission('main.point.claim.confirm') && $mitraRewardClaim->is_pending);
@endphp

<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Detail Klaim Reward</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body py-2 px-3">
        <div class="d-block" id="alert-confirm-container"></div>
        <div class="row g-2 fs-auto">
            <div class="col-5 col-sm-4 d-flex justify-content-between"><span>Nama</span><span>:</span></div>
            <div class="col-7 col-sm-8">{{ $mitraRewardClaim->user->name }}</div>
            <div class="col-5 col-sm-4 d-flex justify-content-between"><span>Username</span><span>:</span></div>
            <div class="col-7 col-sm-8">{{ $mitraRewardClaim->user->username }}</div>
            <div class="col-5 col-sm-4 d-flex justify-content-between"><span>No. Handphone</span><span>:</span></div>
            <div class="col-7 col-sm-8">{{ $mitraRewardClaim->user->phone }}</div>
            <div class="col-5 col-sm-4 d-flex justify-content-between"><span>Poin Yang Diklaim</span><span>:</span></div>
            <div class="col-7 col-sm-8">@formatNumber($mitraRewardClaim->reward->point, 0)</div>
            <div class="col-5 col-sm-4 d-flex justify-content-between"><span>Reward</span><span>:</span></div>
            <div class="col-7 col-sm-8">{{ $mitraRewardClaim->reward->reward }}</div>
        </div>
    </div>
    <div class="modal-footer py-1 px-3">
        @if ($canConfirm)
            <form method="POST" action="{{ route('main.point.claim.confirm', ['confirmMitraRewardClaim' => $mitraRewardClaim->id]) }}" data-alert-container="#alert-confirm-container">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid @yield('submitIcon') me-1"></i>
                    Konfirmasi
                </button>
            </form>
        @endif
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-1"></i>
            Tutup
        </button>
    </div>
</div>
