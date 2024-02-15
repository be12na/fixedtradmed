<form class="modal-content" method="POST"
    action="{{ route('main.settings.quota.update', ['quotaPackage' => $quotaPackageId]) }}" id="myForm"
    data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Edit Quota Belanja</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <input type="hidden" name="package_id" value="{{ $quotaPackageId }}">
        <div class="d-block" id="alert-container"></div>
        <div class="row" style="--bs-gutter-y: 0.5rem; --bs-gutter-x: 0.5rem;">
            <div class="col-12">
                <label class="d-block required">Paket</label>
                <div class="form-control">{{ $packageName }}</div>
            </div>
            <div class="col-12">
                <label class="d-block required">Kuota Belanja</label>
                <input type="number" class="form-control" name="quota" id="quota" value="{{ $quota }}"
                    min="0" placeholder="Kuota Belanja" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Poin Belanja</label>
                <input type="number" class="form-control" name="point" id="point" value="{{ $point }}"
                    min="0" placeholder="Poin Belanja" autocomplete="off" required>
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
