@php
    $pkgRange = $data && $data->package_range > 0 ? $data->package_range : \App\Models\Product::lastPackageRange() + 1;
    $data = optional($data);
@endphp

<form class="modal-content fs-auto" method="POST" action="{{ $postUrl }}" id="myForm" enctype="multipart/form-data"
    data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-container"></div>
        <div class="row gy-2 gx-3">
            <div class="col-md-6">
                <div class="row g-2">
                    <div class="col-12">
                        <label class="d-block">Kategori</label>
                        <select name="product_category_id" id="product_category_id" class="form-select">
                            <option value="0">-- Pilih Kategori --</option>
                            @foreach ($categories as $row)
                                <option value="{{ $row->id }}" @optionSelected($row->id, $data->product_category_id ?? $selectedCategoryId)>{{ $row->merek }} -
                                    {{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="d-block">Kode</label>
                        <input type="text" class="form-control" name="code" id="code"
                            value="{{ $data->code }}" placeholder="Kode" autocomplete="off" required>
                    </div>
                    <div class="col-12">
                        <label class="d-block">Nama</label>
                        <input type="text" class="form-control" name="name" id="name"
                            value="{{ $data->name }}" placeholder="Nama" autocomplete="off" required>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">Satuan Produk</label>
                        <select name="satuan" id="satuan" class="form-select">
                            @foreach (PRODUCT_UNITS as $unitId => $unitName)
                                <option value="{{ $unitId }}" @optionSelected($unitId, $data->satuan ?? 0)>
                                    {{ strtoupper($unitName) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6" id="select-for-box">
                        <label class="d-block">Isi / Box</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="isi" id="isi"
                                value="{{ $data->isi ?? 5 }}" placeholder="Isi / Box" min="1" autocomplete="off"
                                required>
                            <label class="input-group-text">Pcs</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="d-block">Harga Pokok Penjualan</label>
                        <div class="input-group">
                            <label class="input-group-text">Rp</label>
                            <input type="number" class="form-control" name="eceran_d" id="eceran_d"
                                value="{{ $data->hpp ?? 0 }}" placeholder="Harga Pokok Penjualan" min="0"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">Harga Satuan</label>
                        <div class="input-group">
                            <label class="input-group-text">Rp</label>
                            <input type="number" class="form-control" name="harga_a" id="harga_a"
                                value="{{ $data->sell_price ?? 0 }}" placeholder="Harga Satuan" min="0"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">Harga Dropshipper</label>
                        <div class="input-group">
                            <label class="input-group-text">Rp</label>
                            <input type="number" class="form-control" name="harga_b" id="harga_b"
                                value="{{ $data->dropshipper_price ?? 0 }}" placeholder="Harga Pokok Penjualan"
                                min="0" autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">Harga Reseller</label>
                        <div class="input-group">
                            <label class="input-group-text">Rp</label>
                            <input type="number" class="form-control" name="harga_c" id="harga_c"
                                value="{{ $data->reseller_price ?? 0 }}" placeholder="Harga Reseller" min="0"
                                autocomplete="off" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="d-block">Harga Distributor</label>
                        <div class="input-group">
                            <label class="input-group-text">Rp</label>
                            <input type="number" class="form-control" name="harga_d" id="harga_d"
                                value="{{ $data->distributor_price ?? 0 }}" placeholder="Harga Distributor"
                                min="0" autocomplete="off" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="d-block border-top border-top-dark"></div>
            </div>
            <div class="col-md-6">
                <div class="d-block text-center text-md-start mb-1">
                    <span>Klik kotak dibawah untuk </span>
                    <span>mengunggah gambar</span>
                </div>
                <label class="d-flex justify-content-center p-2 border rounded"
                    style="cursor: pointer; height:244px;">
                    <input type="file" name="image" class="d-none" id="image"
                        accept="image/jpeg,image/png" onchange="previewImage(this, '#preview-image')">
                    <div class="d-flex flex-fill align-items-center justify-content-center p-1 border">
                        <img alt="Gambar Produk" id="preview-image" src="{{ $data->image_url }}"
                            style="height:auto; max-height:90%; width:auto; max-width:90%;">
                    </div>
                </label>
            </div>
            <div class="col-md-6">
                <label class="d-block text-center text-md-start mb-1">Keterangan</label>
                <textarea class="form-control" name="notes" id="notes">{{ $data->notes ?? '' }}</textarea>
            </div>
        </div>
    </div>
    <div class="modal-footer py-1">
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="published" name="is_publish" value="1"
                autocomplete="off" @if ($data->is_publish === true) checked @endif>
            <label for="published" class="form-check-label">Publish</label>
        </div>
        <div class="ms-auto">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-save me-1"></i>
                Simpan
            </button>
            <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
                <i class="fa-solid fa-undo me-1"></i>
                Batal
            </button>
        </div>
    </div>
</form>

<script>
    $(function() {
        const notesEditor = new Simditor({
            textarea: $('textarea#notes'),
            toolbar: [
                'bold',
                'italic',
                'underline',
                'strikethrough',
                'color',
                'blockquote',
                'code',
                'link',
            ]
        });

        toggleUnitVolume();
    });
</script>
