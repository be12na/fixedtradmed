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
    </style>
</head>
<body>
    @php
        $tanggal = formatFullDate($startDate);
        if ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')) {
            $tanggal .= ' s/d ' . formatFullDate($endDate);
        }
        $spanHeader = 7;
        if (!empty($branch)) $spanHeader -= 1;
        if (!empty($manager)) $spanHeader -= 1;
    @endphp
    <table class="table">
        <thead class="cell-bold">
            <tr>
                <th class="header-title" colspan="{{ $spanHeader }}">{{ $titleHeader }}</th>
            </tr>
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">{{ $tanggal }}</th>
            </tr>
            @if (!empty($branch))
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">Cabang: {{ $branch->name }}</th>
            </tr>
            @endif
            @if (!empty($manager))
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">Manager: {{ $manager->name }}</th>
            </tr>
            @endif
            <tr>
                <th colspan="{{ $spanHeader }}" class="space"></th>
            </tr>
            <tr>
                <th rowspan="2" class="cell-border">No</th>
                @if (empty($manager))
                    <th rowspan="2" class="cell-border">Manager</th>
                @endif
                @if (empty($branch))
                    <th rowspan="2" class="cell-border">Cabang</th>
                @endif
                <th rowspan="2" class="cell-border">Product</th>
                <th colspan="2" class="cell-border">QTY</th>
                <th rowspan="2" class="cell-border">
                    <div>Total Omzet</div>
                    <div class="small">(Rp)</div>
                </th>
            </tr>
            <tr>
                <th class="cell-border">Box</th>
                <th class="cell-border">Pcs</th>
            </tr>
        </thead>
        <tbody>
            @php
                $noUrut = 1;
                $totalBox = 0;
                $totalPcs = 0;
                $totalOmzet = 0;
            @endphp
            @foreach ($rows as $row)
                <tr>
                    <td class="cell-right cell-border">{{ $noUrut++ }}.</td>
                    @if (empty($manager))
                        <td class="cell-border">{{ $row->manager_name }} ({{ $row->username }})</td>
                    @endif
                    @if (empty($branch))
                        <td class="cell-border">{{ $row->branch_name }}</td>
                    @endif
                    @php
                        $box = intval($row->qty_box);
                        $pcs = intval($row->qty_pcs);
                        $omzet = intval($row->total_omzet);
                        $totalBox += $box;
                        $totalPcs += $pcs;
                        $totalOmzet += $omzet;
                    @endphp

                    <td class="cell-border">{{ $row->product_name }}</td>
                    <td class="cell-right cell-border">@formatNumber($box, 0)</td>
                    <td class="cell-right cell-border">@formatNumber($pcs, 0)</td>
                    <td class="cell-right cell-border">@formatNumber($omzet, 0)</td>
                </tr>
            @endforeach
            {{-- @foreach ($rows->groupBy('manager_id')->sortBy('manager_name') as $managers)
                @php
                    $managerName = $managers->first()->manager_name;
                    $branchesManager = $managers->groupBy('branch_id')->values();
                    $managerSpanned = false;
                @endphp
                @foreach ($branchesManager->sortBy('branch_name') as $branches)
                    @php
                        $branchName = $branches->first()->branch_name;
                        $branchProducts = $branches->groupBy('product_id')->values();
                        $branchSpanned = false;
                    @endphp
                    @foreach ($branchProducts->sortBy('product_name') as $products)
                        <tr>
                            <td class="cell-right cell-border">{{ ($managerSpanned !== true) ? $noUrut . '.' : '' }}</td>
                            @if (empty($manager))
                                <td class="cell-border">{{ ($managerSpanned !== true) ? $managerName : '' }}</td>
                            @endif
                            @php
                                $managerSpanned = true;
                            @endphp
                            @if (empty($branch))
                                <td class="cell-border">{{ ($branchSpanned !== true) ? $branchName : '' }}</td>
                            @endif
                            @php
                                $branchSpanned = true;
                            @endphp
                            @php
                                $box = $products->sum('qty_box');
                                $pcs = $products->sum('qty_pcs');
                                $omzet = $products->sum('total_omzet');
                                $totalBox += $box;
                                $totalPcs += $pcs;
                                $totalOmzet += $omzet;
                            @endphp

                            <td class="cell-border">{{ $products->first()->product_name }}</td>
                            <td class="cell-right cell-border">@formatNumber($box, 0)</td>
                            <td class="cell-right cell-border">@formatNumber($pcs, 0)</td>
                            <td class="cell-right cell-border">@formatNumber($omzet, 0)</td>
                        </tr>
                    @endforeach
                @endforeach
                @php
                    $noUrut++;
                @endphp
            @endforeach --}}
        </tbody>
        <tfoot>
            @php
                $footerSpan = 4;
                if (!empty($branch)) $footerSpan -= 1;
                if (!empty($manager)) $footerSpan -= 1;
            @endphp
            <tr>
                <td class="cell-border cell-right cell-bold" colspan="{{ $footerSpan }}">Total</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalBox, 0)</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalPcs, 0)</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalOmzet, 0)</td>
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