<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-container"></div>
        <div class="row" style="--bs-gutter-y: 0.5rem; --bs-gutter-x: 0.5rem;">
            <div class="col-md-6">
                <label class="d-block required">Kode</label>
                <input type="text" class="form-control" name="code" id="code" value="{{ optional($data)->code }}" placeholder="Kode" autocomplete="off" required>
            </div>
            <div class="col-md-6">
                <label class="d-block required">Merek</label>
                <input type="text" class="form-control" name="merek" id="merek" value="{{ optional($data)->merek }}" placeholder="Merek" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Nama</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ optional($data)->name }}" placeholder="Nama" autocomplete="off" required>
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