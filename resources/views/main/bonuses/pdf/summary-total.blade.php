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
        .table tbody {
            border: 1px solid #212529;
            page-break-before: always;
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
            border: 1px solid #9a9a9a;
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
        // $spanHeader = 16;
        $spanHeader = 10;
    @endphp
    <table class="table">
        <thead class="cell-bold">
            <tr>
                <th class="header-title" colspan="{{ $spanHeader }}">{{ $titleHeader }}</th>
            </tr>
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">{{ $tanggal }}</th>
            </tr>
            <tr>
                <th colspan="{{ $spanHeader }}" class="space"></th>
            </tr>
            <tr>
                <th rowspan="3" class="cell-border">No</th>
                <th rowspan="3" class="cell-border">Posisi</th>
                <th rowspan="3" class="cell-border">Anggota</th>
                {{-- <th colspan="12" class="cell-border">Produk QTY dan Bonus</th> --}}
                <th colspan="6" class="cell-border">Produk QTY dan Bonus</th>
                <th rowspan="3" class="cell-border">Total</th>
            </tr>
            <tr>
                {{-- <th colspan="3" class="cell-border">Royalty</th>
                <th colspan="3" class="cell-border">Override</th> --}}
                <th colspan="3" class="cell-border">Team</th>
                <th colspan="3" class="cell-border">Penjualan</th>
            </tr>
            <tr>
                {{-- <th class="cell-border">Box</th>
                <th class="cell-border">Pcs</th>
                <th class="cell-border">Bonus</th>
                <th class="cell-border">Box</th>
                <th class="cell-border">Pcs</th>
                <th class="cell-border">Bonus</th> --}}
                <th class="cell-border">Box</th>
                <th class="cell-border">Pcs</th>
                <th class="cell-border">Bonus</th>
                <th class="cell-border">Box</th>
                <th class="cell-border">Pcs</th>
                <th class="cell-border">Bonus</th>
            </tr>
        </thead>
        <tbody>
            @php
                $noUrut = 1;
                $totalRoyalty = 0;
                $totalOverride = 0;
                $totalTeam = 0;
                $totalSale = 0;
                $totalBonus = 0;
            @endphp
            @foreach ($rows->sortBy('position_id')->groupBy('position_id') as $positions)
                @php
                    $positionName = $positions->first()->position_name;
                    $positionSpan = $positions->count();
                    $positionSpanned = false;
                @endphp
                @foreach ($positions->sortBy('name') as $member)
                    <tr>
                        <td class="cell-right cell-border">{{ ($positionSpanned !== true) ? $noUrut . '.' : '' }}</td>
                        <td class="cell-border">{{ ($positionSpanned !== true) ? $positionName : '' }}</td>
                        @php
                            $positionSpanned = true;
                        @endphp
                        
                        <td class="cell-border">{{ $member->name }}</td>
                        {{-- <td class="cell-right cell-border">@formatNumber($member->box_royalty, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->pcs_royalty, 0)</td>
                        <td class="cell-right cell-border cell-bold">@formatNumber($royalty = $member->royalty, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->box_override, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->pcs_override, 0)</td>
                        <td class="cell-right cell-border cell-bold">@formatNumber($override = $member->override, 0)</td> --}}
                        <td class="cell-right cell-border">@formatNumber($member->box_team, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->pcs_team, 0)</td>
                        <td class="cell-right cell-border cell-bold">@formatNumber($team = $member->team, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->box_sale, 0)</td>
                        <td class="cell-right cell-border">@formatNumber($member->pcs_sale, 0)</td>
                        <td class="cell-right cell-border cell-bold">@formatNumber($sale = $member->sale, 0)</td>
                        <td class="cell-right cell-border cell-bold">@formatNumber($total = $member->total_bonus, 0)</td>

                        @php
                            // $totalRoyalty += $royalty;
                            // $totalOverride += $override;
                            $totalTeam += $team;
                            $totalSale += $sale;
                            $totalBonus += $total;
                        @endphp
                    </tr>
                @endforeach
                @php
                    $noUrut++;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="cell-border cell-right cell-bold" colspan="3">Total</td>
                <td class="cell-border"></td>
                <td class="cell-border"></td>
                {{-- <td class="cell-border cell-right cell-bold">@formatNumber($totalRoyalty, 0)</td>
                <td class="cell-border"></td>
                <td class="cell-border"></td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalOverride, 0)</td>
                <td class="cell-border"></td>
                <td class="cell-border"></td> --}}
                <td class="cell-border cell-right cell-bold">@formatNumber($totalTeam, 0)</td>
                <td class="cell-border"></td>
                <td class="cell-border"></td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalSale, 0)</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalBonus, 0)</td>
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