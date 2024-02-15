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
                <label class="d-block required">Bank</label>
                @php
                    $currentBankId = $data->bank_code ?? '';
                @endphp
                <select name="bank_code" class="form-select">
                    <option value="">-- Pilih Bank --</option>
                    @foreach (BANK_LIST as $code => $name)
                        <option value="{{ $code }}" @optionSelected($code, $currentBankId)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="d-block required">Nama Pemilik</label>
                <input type="text" class="form-control" name="account_name" id="name" value="{{ $data->account_name }}" placeholder="Nama Pemilik" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">No. Rekening</label>
                <input type="text" class="form-control" name="account_no" id="name" value="{{ $data->account_no }}" placeholder="No. Rekening" autocomplete="off" required>
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