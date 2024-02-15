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
        $spanHeader = 5;
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
                <th class="cell-border">No</th>
                @if (empty($manager))
                    <th class="cell-border">Distributor</th>
                @endif
                @if (empty($branch))
                    <th class="cell-border">Cabang</th>
                @endif
                <th class="cell-border">
                    <div>Total Omzet</div>
                    <div class="small">(Rp)</div>
                </th>
                <th class="cell-border">
                    <div>Total Bonus</div>
                    <div class="small">(Rp)</div>
                </th>
            </tr>
        </thead>
        <tbody>
            @php
                $noUrut = 1;
                $totalOmzet = 0;
                $totalBonus = 0;
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
                        $omzet = intval($row->total_omzet);
                        $bonus = intval($row->total_bonus);
                        $totalOmzet += $omzet;
                        $totalBonus += $bonus;
                    @endphp

                    <td class="cell-right cell-border">@formatNumber($omzet, 0)</td>
                    <td class="cell-right cell-border">@formatNumber($bonus, 0)</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $footerSpan = 3;
                if (!empty($branch)) $footerSpan -= 1;
                if (!empty($manager)) $footerSpan -= 1;
            @endphp
            <tr>
                <td class="cell-border cell-right cell-bold" colspan="{{ $footerSpan }}">Total</td>
                <td class="cell-border cell-right cell-bold">@formatNumber($totalOmzet, 0)</td>
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