<form class="modal-content remote-submit" method="POST" action="{{ route('main.withdraw.transfer.submit') }}" data-alert-container="#alert-from-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Konfirmasi</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body fs-auto">
        @csrf
        <div class="d-block" id="alert-from-container"></div>
        <input type="hidden" name="type" value="{{ $bonusType }}">
        @foreach ($checks as $check)
            <input type="hidden" name="checks[]" value="{{ $check }}">
        @endforeach
        <div class="d-block">
            Benarkah data yang dipilih adalah data yang sudah ditransfer ?
        </div>
    </div>
    <div class="modal-footer justify-content-center py-1">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-check me-2"></i>Ya
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa fa-times me-2"></i>Tidak
        </button>
    </div>
</form>