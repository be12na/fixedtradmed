<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Detail Transfer</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body fs-auto py-2 px-3">
        <table class="table mb-0">
            <tr>
                <td class="fw-bold">Tanggal</td>
                <td class="text-end fw-bold">{{ $transfer->transfer_date }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Cabang</td>
                <td class="text-end fw-bold">{{ $transfer->branch->name }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Manager</td>
                <td class="text-end fw-bold">{{ $transfer->manager->name }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Total Penjualan</td>
                <td class="text-end">@formatCurrency($transfer->total_omzets, 0, true)</td>
            </tr>
            {{-- <tr>
                <td class="fw-bold">Foundation</td>
                <td class="text-end">@formatCurrency($transfer->total_crews, 0, true)</td>
            </tr>
            <tr>
                <td class="fw-bold">Profit Crew</td>
                <td class="text-end">@formatCurrency($transfer->total_foundations, 0, true)</td>
            </tr> --}}
            {{-- <tr>
                <td class="fw-bold">Savings</td>
                <td class="text-end">@formatCurrency($transfer->total_savings, 0, true)</td>
            </tr> --}}
            {{-- <tr>
                <td class="fw-bold">Potongan</td>
                <td class="text-end">@formatCurrency($transfer->discount_amount, 0, true)</td>
            </tr> --}}
            {{-- <tr>
                <td class="fw-bold">Penggunaan Omzet</td>
                <td class="text-end">@formatCurrency($transfer->omzet_used, 0, true)</td>
            </tr> --}}
            <tr>
                <td class="fw-bold">Total Transfer</td>
                <td class="text-end fw-bold">@formatCurrency($transfer->total_transfer, 0, true)</td>
            </tr>
            
            @if ($transfer->bank_id == 0)
                <tr>
                    <td class="fw-bold">Bank</td>
                    <td class="text-end">{{ $transfer->bank_code }}</td>
                </tr>
            @else
                <tr>
                    <td class="fw-bold">Bank</td>
                    <td class="text-end">{{ $transfer->bank_name }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Rekening</td>
                    <td class="text-end">{{ $transfer->account_no }}</td>
                </tr>
                <tr>
                    <td class="fw-bold">Nama Rekening</td>
                    <td class="text-end">{{ $transfer->account_name }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="px-0">
                        <div class="d-block text-center fw-bold mb-2">Bukti Transfer</div>
                        <div class="d-flex align-items-center justify-content-center p-1 border rounded-3 bg-gray-300" style="min-height:150px;">
                            <img alt="Bukti Transfer" src="{{ $transfer->image_url }}" style="height:auto; width:auto; max-width:100%;">
                        </div>
                    </td>
                </tr>
            @endif

            <tr>
                <td class="fw-bold">Ket. Transfer</td>
                <td class="text-end">{{ $transfer->transfer_note ?? '-' }}</td>
            </tr>
            
            @php
                $color = 'bg-warning';
                if ($transfer->transfer_status == PROCESS_STATUS_APPROVED) {
                    $color = 'bg-success text-light';
                } elseif ($transfer->transfer_status == PROCESS_STATUS_REJECTED) {
                    $color = 'bg-danger text-light';
                }
            @endphp
            <tr>
                <td class="fw-bold">Status</td>
                <td class="text-end"><span class="py-1 px-2 {{ $color }}">{{ $transfer->status_text }}</span></td>
            </tr>
            @if ($transfer->transfer_status == PROCESS_STATUS_REJECTED)
                <tr>
                    <td class="fw-bold">Ket. Status</td>
                    <td class="text-end"><span class="py-1 px-2">{{ $transfer->status_note }}</span></td>
                </tr>
            @endif
        </table>
    </div>
    <div class="modal-footer py-1 px-3">
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-1"></i>
            Tutup
        </button>
    </div>
</div>