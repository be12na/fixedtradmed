@php
    $data = optional($data);
@endphp

<form class="modal-content" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-dicount-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="mitra_type" value="{{ MITRA_TYPE_AGENT }}">
        <div class="d-block" id="alert-dicount-container"></div>
        <div class="row g-2">
            <div class="col-12">
                <label class="d-block required">Zona</label>
                <select name="zone_id" class="form-select" autocomplete="off" required>
                    @foreach ($zones as $zone)
                        <option value="{{ $zone->id }}" @optionSelected($zone->id, $data->zone_id ?? $selectedZoneId)>{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="d-block required">Kategori</label>
                <select name="discount_category" class="form-select" autocomplete="off" required>
                    @foreach (MITRA_DISCOUNT_CATEGORIES as $category_id => $category_name)
                        @php
                            if ($category_id == 0) continue;
                        @endphp
                        <option value="{{ $category_id }}" @optionSelected($category_id, $data->discount_category ?? 0)>{{ $category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="d-block required">QTY</label>
                <input type="number" class="form-control" name="min_qty" value="{{ $data->min_qty ?? 0 }}" min="0" step="1" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Diskon</label>
                <div class="input-group">
                    <label class="input-group-text">Rp</label>
                    <input type="number" class="form-control" name="discount" value="{{ $data->discount ?? 0 }}" min="0" step="100" autocomplete="off" required>
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
