<form class="modal-content" method="POST" action="{{ route('mitra.point.reward.claim.save', ['point' => $mitraReward->point]) }}" id="myConfirm" data-alert-container="#alert-confirm-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Konfirmasi</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body py-2 px-3">
        @csrf
        <div class="d-block" id="alert-confirm-container"></div>
        <div>
            Anda akan mengambil reward dengan menggunakan poin sebanyak <b>@formatNumber($mitraReward->point, 0)</b> poin untuk:
        </div>
        <div class="fw-bold text-decoration-underline">
            {{ $mitraReward->reward }}
        </div>
    </div>
    <div class="modal-footer justify-content-center py-1 px-3">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-check me-1"></i>
            Klaim
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>