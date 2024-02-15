<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }}</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        <div class="d-block" id="alert-detail-container"></div>
        <div class="d-block w-100 border overflow-x-auto">
            <table class="table table-sm table-striped table-nowrap mb-2 fs-auto">
                <tbody>
                    <tr>
                        <td>Username</td>
                        <td class="text-end">{{ $data->username }}</td>
                    </tr>
                    <tr>
                        <td>Identitas</td>
                        <td class="text-end">{{ $data->identity }}</td>
                    </tr>
                    <tr>
                        <td>Nama</td>
                        <td class="text-end">{{ $data->name }}</td>
                    </tr>
                    <tr>
                        <td>Paket</td>
                        <td class="text-end">
                            <input type="hidden" id="mitra-type-select" value="{{ $data->mitra_type }}">
                            {{ $data->mitra_type_name }}
                        </td>
                    </tr>
                    <tr>
                        <td>Referral</td>
                        <td class="text-end">{{ $data->referral->name }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td class="text-end">{{ $data->email }}</td>
                    </tr>
                    <tr>
                        <td>Handphone</td>
                        <td class="text-end">{{ $data->phone }}</td>
                    </tr>
                    @if ($data->userPackage->total_price > 0)
                        <tr>
                            <td>Bank</td>
                            <td class="text-end">{{ $data->userPackage->bank_name }}</td>
                        </tr>
                        <tr>
                            <td>No. Rekening</td>
                            <td class="text-end">{{ $data->userPackage->account_no }}</td>
                        </tr>
                        <tr>
                            <td>Pemilik Rekening</td>
                            <td class="text-end">{{ $data->userPackage->account_name }}</td>
                        </tr>
                        <tr>
                            <td>Total Transfer</td>
                            <td class="text-end">@formatCurrency($data->userPackage->total_price)</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer py-1">
        @hasPermission('main.mitra.register.action')
        <button type="button" class="btn btn-sm btn-primary" onclick="openConfirmModal($(this));" data-form-action="{{ $postUrl }}" data-confirm-text="Terima" data-confirm-value="confirm">
            <i class="fa-solid fa-handshake me-1"></i>
            Terima
        </button>
        <button type="button" class="btn btn-sm btn-danger" onclick="openConfirmModal($(this));" data-form-action="{{ $postUrl }}" data-confirm-text="Tolak" data-confirm-value="reject">
            <i class="fa-solid fa-times me-1"></i>
            Tolak
        </button>
        @endhasPermission
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</div>