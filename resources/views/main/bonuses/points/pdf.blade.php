<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $filename }}</title>
    <style>
        @page {
            margin: 10pt 10pt 20pt;
        }
        body {
            margin: 10pt 10pt 20pt;
        }
        * {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 12pt;
        }
        .header-title {
            font-size: 16pt;
        }
        .header-sub-title {
            font-size: 12pt;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th,
        .table td {
            padding: 2pt 4pt;
        }
        .table th.space,
        .table td.space {
            padding: 5pt;
        }
        .table thead th {
            text-align: center;
            vertical-align: middle;
        }
        .table tbody td {
            vertical-align: top;
        }
        .cell-bold {
            font-weight: bold;
        }
        .cell-right {
            text-align: right;
        }
        .cell-border {
            border: 1px solid #212529;
        }
        .small {
            font-size: 8pt;
        }
        .italic {
            font-style: italic;
        }
        .text-primary {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    @php
        $tanggal = formatFullDate($startDate);
        if ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')) {
            $tanggal .= ' s/d ' . formatFullDate($endDate);
        }
        $spanHeader = 5;
        if ($isShoppingPoint) {
            $spanHeader += 1;
        }
    @endphp
    <table class="table">
        <thead class="cell-bold">
            <tr>
                <th class="header-title" colspan="{{ $spanHeader }}">{{ $titleHeader }}</th>
            </tr>
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">Tanggal {{ $tanggal }}</th>
            </tr>
            <tr>
                <th colspan="{{ $spanHeader }}" class="space"></th>
            </tr>
            <tr>
                <th class="cell-border">No</th>
                <th class="cell-border">Tanggal</th>
                <th class="cell-border">Member</th>
                @if (!$isShoppingPoint)
                    <th class="cell-border">Sumber</th>
                @endif
                <th class="cell-border">No. Transaksi</th>
                @if ($isShoppingPoint)
                    <th class="cell-border">Produk</th>
                    <th class="cell-border">Jumlah</th>
                @endif
                <th class="cell-border">Poin</th>
            </tr>
        </thead>
        <tbody>
            @php
                $noUrut = 1;
                $totalPoin = 0;
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td class="cell-right cell-border">{{ $noUrut++ }}.</td>
                    <td class="cell-border">@formatFullDate($row->point_date)</td>
                    <td class="cell-border">
                        <div>{{ $row->user->name }}</div>
                        <div class="small italic text-primary">({{ $row->user->username }})</div>
                    </td>
                    @if (!$isShoppingPoint)
                        <td class="cell-border">
                            <div>{{ $row->fromUser ? $row->fromUser->name : '-' }}</div>
                            @if ($row->fromUser)
                                <div class="small italic text-primary">({{ $row->fromUser->username }})</div>
                            @endif
                        </td>
                    @endif
                    @php
                        $purchase = $isShoppingPoint ? $row->purchase : $row->userPackage;
                    @endphp
                    <td class="cell-border">{{ $purchase->code }}</td>
                    @if ($isShoppingPoint)
                        <td class="cell-border">{{ $row->purchaseProduct->product->name }}</td>
                        <td class="cell-right cell-border">@formatNumber($row->product_qty, 0)</td>
                    @endif
                    <td class="cell-right cell-border">@formatNumber($row->point, 0)</td>
                    @php
                        $totalPoin += $row->point;
                    @endphp
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $footerSpan = 5;
                if ($isShoppingPoint) $footerSpan += 1;
            @endphp
            <tr>
                <td class="cell-border cell-right cell-bold" colspan="{{ $footerSpan }}">Total Poin</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalPoin, 0)</td>
            </tr>
        </tfoot>
    </table>

    {{-- page number --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "- {PAGE_NUM} -";
            $size = 8;
            $font = "{{ config('dompdf.defines.default_font') }}";
            $width = $fontMetrics->get_text_width($text, $font, $size) / 4;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>