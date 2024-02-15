<div class="d-block border-bottom py-1 px-2 bg-gray-200 fw-bold" style="--bs-bg-opacity:0.25;">Cabang: {{ $branch->name }}</div>

<table class="table table-sm table-nowrap table-hover mb-0 fs-auto" style="min-width: 100%;">
    <thead class="bg-gradient bg-light align-middle text-center">
        <tr>
            <th rowspan="2">Tanggal</th>
            <th class="border-start" rowspan="2">Hari</th>
            <th class="border-start" colspan="2">Quantity</th>
        </tr>
        <tr>
            <th class="border-start">BOX</th>
            <th class="border-start">PCS</th>
        </tr>
    </thead>
    @php
        $totalQtyBox = 0;
        $totalQtyPcs = 0;
        $dataSales = $branch->sales;
        $dataTransfers = $branch->branchTransfers;
    @endphp
    <tbody>
        @if ($dataSales->isNotEmpty())
            @foreach ($dataSales->groupBy('sale_date') as $tgl => $sales)
                @php
                    $qtyPerDayBox = $sales->sum('sum_quantity_box');
                    $qtyPerDayPcs = $sales->sum('sum_quantity_pcs');
                    $totalQtyBox += $qtyPerDayBox;
                    $totalQtyPcs += $qtyPerDayPcs;
                @endphp
                <tr>
                    <td>{{ $tgl }}</td>
                    <td>@dayName($sales->first()->getRawOriginal('sale_date'))</td>
                    <td class="text-end">@formatNumber($qtyPerDayBox, 0)</td>
                    <td class="text-end">@formatNumber($qtyPerDayPcs, 0)</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="3" class="text-center">{{ __('datatable.emptyTable') }}</td>
            </tr>
        @endif
    </tbody>
    <tfoot class="border-top-dark border-top-2" style="border-top-width: 2px;">
        <tr>
            <td class="fw-bold border-bottom-dark">Jumlah Produk</td>
            <td class="border-bottom-dark"></td>
            <td class="text-end fw-bold border-bottom-dark">@formatNumber($totalQtyBox, 0)</td>
            <td class="text-end fw-bold border-bottom-dark">@formatNumber($totalQtyPcs, 0)</td>
        </tr>
        <tr>
            <td colspan="4" class="fw-bold">Summary</td>
        </tr>
        @php
            $transferCash = $dataTransfers->where('bank_code', '=', BANK_000)->sum('total_transfer');
            $totalTransfer = $transferCash;
            $bankNo = 1;
        @endphp
        <tr>
            <td class="fw-bold">Transfer</td>
            <td>
                <span class="d-inline-block me-3">{{ $bankNo++; }}.</span>
                <span class="d-inline-block">{{ BANK_000 }}</span>
            </td>
            <td class="text-end fw-bold" colspan="2">@formatCurrency($transferCash, 0, true)</td>
        </tr>
        @foreach ($bankCodes as $bank)
            @php
                $transferAmount = $dataTransfers->where('bank_code', '=', $bank)->sum('total_transfer');
                $totalTransfer += $transferAmount;
            @endphp
            <tr>
                <td></td>
                <td>
                    <span class="d-inline-block me-3">{{ $bankNo++; }}.</span>
                    <span class="d-inline-block">{{ $bank }}</span>
                </td>
                <td class="text-end fw-bold" colspan="2">@formatCurrency($transferAmount, 0, true)</td>
            </tr>
        @endforeach
        
        @if (count($bankCodes) > 0)
            <tr>
                <td class="fw-bold border-top-dark">Total Transfer</td>
                <td></td>
                <td class="text-end fw-bold" colspan="2">@formatCurrency($totalTransfer, 0, true)</td>
            </tr>
        @endif
    </tfoot>
</table>