<form class="modal-content fs-auto" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold">Persediaan Produk</span>
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
            @php
                $labelWidth = '150px';
            @endphp
            <div class="d-flex flex-nowrap mb-1">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Cabang
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap fw-bold">{{ $branch->name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Kategori
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap fw-bold">{{ $product->category->name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Produk
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap fw-bold">{{ $product->name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Satuan
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap fw-bold">{{ $product->product_unit }}</div>
            </div>
            @if ($product->satuan == PRODUCT_UNIT_BOX)
            <div class="d-flex flex-nowrap mb-1">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Isi
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap fw-bold">@formatNumber($product->isi) {{ $product->volume_product_unit }}</div>
            </div>
            @endif
            <div class="d-flex flex-nowrap mb-2">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Jml. Sebelumnya
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1 text-wrap">
                    @php
                        $prev = ($inputModes == STOCK_FLAG_EDIT) ? $lastStock->rest_stock : $lastStock->total_stock;
                    @endphp
                    @formatNumber($prev)
                </div>
            </div>
            
            @if ($inputModes == STOCK_FLAG_ADMIN)
                <div class="d-flex flex-nowrap mb-2">
                    <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                        Jml. Output
                        <span class="position-absolute end-0 me-1">:</span>
                    </div>
                    <div class="flex-fill ms-1 text-wrap">@formatNumber($lastStock->total_output)</div>
                </div>
                <div class="d-flex flex-nowrap mb-2">
                    <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                        Jml. Sisa
                        <span class="position-absolute end-0 me-1">:</span>
                    </div>
                    <div class="flex-fill ms-1 text-wrap">@formatNumber($lastStock->rest_stock)</div>
                </div>
            @endif

            @if ($inputModes == STOCK_FLAG_EDIT)
                <div class="d-flex flex-nowrap mb-2">
                    <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                        Jml. Input
                        <span class="position-absolute end-0 me-1">:</span>
                    </div>
                    <div class="flex-fill ms-1 text-wrap">@formatNumber($lastStock->input_manager)</div>
                </div>
                <div class="d-flex flex-nowrap mb-2">
                    <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                        Selisih
                        <span class="position-absolute end-0 me-1">:</span>
                    </div>
                    <div class="flex-fill ms-1 text-wrap">@formatNumber($lastStock->diff_stock)</div>
                </div>
            @endif

            @php
                $value = 0;
                $inputTypeText = 'Buat baru';
                if ($inputModes == STOCK_FLAG_EDIT) {
                    $value = $lastStock->input_manager;
                    $inputTypeText = 'Ubah persediaan';
                } elseif ($inputModes == STOCK_FLAG_PLUS) {
                    $inputTypeText = 'Tambah persediaan';
                } elseif (is_array($inputModes)) {
                    $inputTypeText = '';
                } elseif ($inputModes == STOCK_FLAG_ADMIN) {
                    $value = $lastStock->rest_stock;
                }
            @endphp
            
            <div class="d-flex align-items-center flex-nowrap mb-2">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Jenis Input
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1">
                    @if (is_array($inputModes))
                        <select name="input_mode" id="input_mode" class="form-select">
                            @foreach ($inputModes as $selectIndex => $selectValue)
                                <option value="{{ $selectIndex }}">{{ $selectValue }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="hidden" name="input_mode" id="input_mode" value="{{ $inputModes }}">
                        <div class="flex-fill ms-1 text-wrap">{{ $inputTypeText }}</div>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center flex-nowrap">
                <div class="position-relative" style="flex: 0 0 {{ $labelWidth }};">
                    Masukkan Jumlah
                    <span class="position-absolute end-0 me-1">:</span>
                </div>
                <div class="flex-fill ms-1">
                    <input type="number" class="form-control" name="input_value" id="input_value" min="0" step="1" value="{{ $value }}" placeholder="Jumlah" autocomplete="off" required>
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