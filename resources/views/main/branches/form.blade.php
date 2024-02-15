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
            <div class="col-sm-6">
                <label class="d-block required">Kode</label>
                <input type="text" class="form-control" name="code" id="code" value="{{ optional($data)->code }}" placeholder="Kode" autocomplete="off" required>
            </div>
            @if ($isAppV2)
                <div class="col-sm-6">
                    <label class="d-block required">Zona</label>
                    @php
                        $currentZoneId = optional($data)->zone_id ?? 0;
                    @endphp
                    <select name="zone_id" class="form-select">
                        <option value="0">-- Pilih Zona --</option>
                        @foreach (neo()->zones(true) as $zone)
                            <option value="{{ $zone->id }}" @optionSelected($zone->id, $currentZoneId)>{{ $zone->name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <div class="col-sm-6">
                    <label class="d-block required">Wilayah</label>
                    @php
                        $currentZoneId = optional($data)->wilayah ?? 0;
                    @endphp
                    <select name="wilayah" class="form-select">
                        @foreach (BRANCH_ZONES as $zoneId => $zoneName)
                            <option value="{{ $zoneId }}" @optionSelected($zoneId, $currentZoneId)>{{ $zoneName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-12">
                <label class="d-block required">Nama Cabang</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ optional($data)->name }}" placeholder="Nama" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block">Alamat</label>
                <textarea name="address" id="address" class="form-control" maxlength="250">{{ optional($data)->address }}</textarea>
            </div>
            <div class="col-6 col-sm-4">
                <label class="d-block">Kode Pos</label>
                <input type="text" class="form-control" name="pos_code" id="pos_code" value="{{ optional($data)->pos_code }}" placeholder="Kode Pos" autocomplete="off">
            </div>
            <div class="col-sm-8 d-flex flex-wrap justify-content-sm-around align-items-end">
                <div class="form-check me-4">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" autocomplete="off" @if ((!empty($data) && ($data->is_active === true)) || empty($data)) checked @endif>
                    <label for="is_active" class="form-check-label">Aktif</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_stock" name="is_stock" value="1" autocomplete="off" @if (optional($data)->is_stock === true) checked @endif>
                    <label for="is_stock" class="form-check-label">Stock</label>
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