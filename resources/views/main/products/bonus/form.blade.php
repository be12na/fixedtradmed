<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-dicount-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-dicount-container"></div>
        <div class="row g-2">
            <div class="col-12 text-center">
                <h4>Bonus Member Basic</h4>
                <h5>{{ $product->name }}, {{ $zone->full_name }}</h5>
            </div>
            <div class="col-12">
                <label class="d-block required">Harga</label>
                <div class="input-group">
                    <label class="input-group-text">Rp</label>
                    <div type="number" class="form-control">
                        @formatNumber($data->mitra_price ?? 0, 0)
                    </div>
                </div>
            </div>
            <div class="col-12">
                <label class="d-block required">Member Basic</label>
                <div class="input-group">
                    <label class="input-group-text">Rp</label>
                    <input type="number" class="form-control" name="mitra_basic_bonus" value="{{ $data->mitra_basic_bonus ?? 0 }}" min="0" step="100" autocomplete="off" required>
                </div>
            </div>
            <div class="col-12">
                <label class="d-block required">Member Premium</label>
                <div class="input-group">
                    <label class="input-group-text">Rp</label>
                    <input type="number" class="form-control" name="mitra_premium_bonus" value="{{ $data->mitra_premium_bonus ?? 0 }}" min="0" step="100" autocomplete="off" required>
                </div>
            </div>
            <div class="col-12">
                <label class="d-block required">Distrbutor</label>
                <div class="input-group">
                    <label class="input-group-text">Rp</label>
                    <input type="number" class="form-control" name="distributor_bonus" value="{{ $data->distributor_bonus ?? 0 }}" min="0" step="100" autocomplete="off" required>
                </div>
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
