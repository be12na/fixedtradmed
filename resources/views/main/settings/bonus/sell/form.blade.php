<form class="modal-content" method="POST" action="{{ route('main.settings.bonus.sell.update') }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    @php
        $showSubmit = (!empty($setting) || ($mode == 'new'));
        $data = optional($setting);
    @endphp
    <div class="modal-body">
        @if ($showSubmit)
            @csrf
            <div class="d-block" id="alert-container"></div>
            <div class="d-block">
                <label class="d-block required">Bonus</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="percent" value="{{ $data->percent ?? 0 }}" autocomplete="off" required>
                    <label class="input-group-text">%</label>
                </div>
            </div>
        @else
            <h3 class="text-danger">Data tidak ditemukan</h3>
        @endif
    </div>
    <div class="modal-footer justify-content-between py-1">
        @if ($showSubmit)
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-save me-1"></i>
                Simpan
            </button>
        @endif
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>