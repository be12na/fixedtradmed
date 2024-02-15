<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Detail Pembayaran</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body small py-2 px-3">
        <div class="d-block mb-3" style="--detail-label-width:120px;">
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Kode</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchPayment->code }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Tanggal</span><span>:</span>
                </div>
                <div class="ms-2">@formatFullDate($branchPayment->payment_date, 'd F Y')</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Cabang</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchPayment->branch->name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Manager</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchPayment->manager->name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Status</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchPayment->transfer_status_name }}</div>
            </div>
            @if ($branchPayment->transfer_status == PAYMENT_STATUS_REJECTED)
                <div class="d-flex flex-nowrap mb-1">
                    <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                        <span class="fw-bold">Keterangan</span><span>:</span>
                    </div>
                    <div class="ms-2">{{ $branchPayment->status_note ?? '-' }}</div>
                </div>
            @endif
        </div>
        <div class="d-block @if ($branchPayment->is_approving) mb-3 @endif">
            <div class="d-block w-100 overflow-x-auto border">
                <table class="table table-sm table-nowrap table-detail mb-2">
                    <thead class="bg-gradient-brighten bg-white text-center">
                        <tr>
                            <th class="border-bottom">Produk</th>
                            <th class="border-bottom border-start">Jumlah</th>
                            <th class="border-bottom border-start">Harga</th>
                            <th class="border-bottom border-start">Jumlah</th>
                            <th class="border-bottom border-start">Diskon</th>
                            <th class="border-bottom border-start">Total</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach ($branchPayment->details as $item)
                            <tr>
                                <td class="ps-4">{{ $item->product->name }}</td>
                                <td class="text-center">@formatNumber($item->product_qty, 0) {{ $item->product_unit_name }}</td>
                                <td class="text-end">@formatCurrency($item->product_price, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_price, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_discount, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_price - $item->total_discount, 0, true)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="fw-bold text-end">Total</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_price, 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_discount, 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_transfer, 0, true)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @if ($branchPayment->is_approving)
        <div class="d-block">
            <div class="d-block w-100 overflow-x-auto border">
                <table class="table table-sm table-nowrap table-detail mb-2">
                    @if ($branchPayment->bank_id == 0)
                        <tr>
                            <td class="fw-bold">Bank</td>
                            <td class="text-end">{{ $branchPayment->bank_code }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="fw-bold">Bank</td>
                            <td class="text-end">{{ $branchPayment->bank_name }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Rekening</td>
                            <td class="text-end">{{ $branchPayment->account_no }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Nama Rekening</td>
                            <td class="text-end">{{ $branchPayment->account_name }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" class="px-0">
                                <div class="d-block text-center fw-bold mb-2">Bukti Transfer</div>
                                <div class="d-flex align-items-center justify-content-center p-1 border bg-gray-300" style="min-height:150px;">
                                    <img alt="Bukti Transfer" src="{{ $branchPayment->image_url }}" style="height:auto; width:auto; max-width:100%;">
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td class="fw-bold">Ket. Transfer</td>
                        <td class="text-end">{{ $branchPayment->transfer_note ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>
    <div class="modal-footer justify-content-between py-1 px-3">
        <div>
        @if ($branchPayment->is_transferable)
            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.href='{{ route('member.payment.transfer', ['branchPaymentTransferable' => $branchPayment->id]) }}';">
                <i class="fa-solid fa-money-bill-transfer me-1"></i>Transfer
            </button>
        @endif
        </div>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-1"></i>
            Tutup
        </button>
    </div>
</div>