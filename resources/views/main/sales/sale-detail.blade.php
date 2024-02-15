@php
    $isDelete = (isset($deleteData) && ($deleteData === true));
@endphp

<div class="modal-content">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Detail Penjualan @if($isDelete) : Hapus Data @endif</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body small py-2 px-3">
        @if ($isDelete)
            <div class="fw-bold text-danger mb-3">Yakin akan menghapus data penjualan?</div>
            <div class="d-block" id="alert-delete-container">
                @include('partials.alert')
            </div>
        @endif
        <div class="d-block mb-3" style="--detail-label-width:80px;">
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Kode</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->code }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Tanggal</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->sale_date }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Manager</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->manager_name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Cabang</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->branch_name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Salesman</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->sales_name }}</div>
            </div>
            <div class="d-flex flex-nowrap mb-1">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Posisi</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->sales_position_name }}</div>
            </div>
            <div class="d-flex flex-nowrap">
                <div class="flex-shrink-0 d-flex justify-content-between flex-nowrap" style="flex-basis:var(--detail-label-width);">
                    <span class="fw-bold">Catatan</span><span>:</span>
                </div>
                <div class="ms-2">{{ $branchSale->salesman_note ?? '-' }}</div>
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
                            <th class="border-bottom border-start">Total</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @php
                            $currentCategoryId = null;
                        @endphp
                        @foreach ($productDetails as $detail)
                            @if ($currentCategoryId != $detail->category_id)
                            <tr>
                                <td colspan="4" class="fw-bold">
                                    {{ $detail->category_name }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="ps-4">{{ $detail->product_name }}</td>
                                <td class="text-center">
                                    <span>@formatNumber($detail->product_qty)</span>
                                    <span>{{ $detail->product_unit_name }}</span>
                                </td>
                                <td class="text-end">@formatNumber($detail->product_price)</td>
                                <td class="text-end">@formatNumber($detail->total_price)</td>
                            </tr>
                            @php
                                $currentCategoryId = $detail->category_id;
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="fw-bold text-end">Total</td>
                            <td class="fw-bold text-end">
                                @formatNumber($branchSale->sum_total_price)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="modal-footer @if($isDelete === true) justify-content-between @endif py-1 px-3">
        @if($isDelete === true)
        <form method="POST" action="{{ route('main.sales.destroy', ['branchSaleModifiable' => $branchSale->id]) }}" data-alert-container="#alert-delete-container">
            @csrf
            <button type="submit" class="btn btn-sm btn-danger">
                <i class="fa-solid fa-trash me-1"></i>Hapus Data
            </button>
        </form>
        @endif
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-times me-1"></i>Tutup
        </button>
    </div>
</div>