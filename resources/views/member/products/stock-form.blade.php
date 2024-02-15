<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branch->id }}">
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <div class="d-block" id="alert-container"></div>
        <div class="d-block">
            <label class="d-block required">Jumlah Persediaan</label>
            <input type="number" class="form-control" name="input_manager" id="input_manager" min="0" step="1" value="{{ $currentStock }}" placeholder="Jumlah" autocomplete="off" required>
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