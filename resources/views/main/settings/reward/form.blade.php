<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    @php
        $data = optional($data);
    @endphp
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-container"></div>
        <div class="row" style="--bs-gutter-y: 0.5rem; --bs-gutter-x: 0.5rem;">
            <div class="col-12">
                <label class="d-block required">Poin</label>
                <input type="number" class="form-control" name="point" id="point" value="{{ $data->point }}" placeholder="Minimal Poin" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Reward</label>
                <input type="text" class="form-control" name="reward" id="reward" value="{{ $data->reward }}" placeholder="Keterangan Reward" autocomplete="off" required>
            </div>
        </div>
    </div>
    <div class="modal-footer py-1">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-save me-1"></i>
            Simpan
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>