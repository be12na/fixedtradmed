<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Konfirmasi Data</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body small py-2 px-3">
        <div class="d-block mb-3" style="--detail-label-width:80px;">
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Tanggal</span><span>:</span>
                </div>
                <div class="ms-2">@formatFullDate($values['payment_date'], 'd F Y')</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Cabang</span><span>:</span>
                </div>
                <div class="ms-2">{{ $values['branch']->name }}</div>
            </div>
        </div>
        <div class="d-block">
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
                        @foreach ($values['details'] as $item)
                            <tr>
                                <td class="ps-4">{{ $item['product']->name }}</td>
                                <td class="text-center">@formatNumber($item['product_qty'], 0) {{ $item['product']->product_unit }}</td>
                                <td class="text-end">@formatCurrency($item['product_price'], 0, true)</td>
                                <td class="text-end">@formatCurrency($item['total_price'], 0, true)</td>
                                <td class="text-end">@formatCurrency($item['total_discount'], 0, true)</td>
                                <td class="text-end">@formatCurrency($item['total_price'] - $item['total_discount'], 0, true)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="fw-bold text-end">Total</td>
                            <td class="fw-bold text-end">@formatCurrency($values['total_price'], 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($values['total_discount'], 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($values['sub_total'], 0, true)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <div class="modal-footer justify-content-between py-1 px-3">
        <button type="button" class="btn btn-sm btn-primary" onclick="doSubmit();">
            <i class="fa-solid fa-check me-1"></i>OK
        </button>
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-1"></i>
            Tutup
        </button>
    </div>
</div>