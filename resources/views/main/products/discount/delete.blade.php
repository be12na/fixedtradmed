<form class="modal-content" method="POST" action="{{ route('main.master.product.discount.destroy', ['product' => $product->id, 'mitraDiscount' => $data->id]) }}" id="myForm" data-alert-container="#alert-delete-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-delete-container"></div>
        <div class="text-center">
            Hapus diskon untuk produk <span class="fw-bold">{{ $product->name }}</span> yang jumlah minimal <span class="fw-bold">@formatNumber($data->min_qty, 0)</span>
        </div>
        <div class="text-center">???</div>
    </div>
    <div class="modal-footer py-1">
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="fa-solid fa-trash me-1"></i>
            Hapus
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>
