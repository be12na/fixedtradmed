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
        $spanHeader = 4;
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
                <th class="cell-border">No</th>
                <th class="cell-border">Posisi</th>
                <th class="cell-border">Anggota</th>
                <th class="cell-border">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $noUrut = 1;
                $totalBonus = 0;
            @endphp
            @foreach ($rows->sortBy(['position_id' => 'asc'])->groupBy('position_id') as $positions)
                @php
                    $positionName = $positions->first()->position_name;
                    $positionSpanned = false;
                @endphp
                @foreach ($positions->sortBy(['name' => 'asc']) as $member)
                    <tr>
                        <td class="cell-right cell-border">{{ ($positionSpanned !== true) ? $noUrut . '.' : '' }}</td>
                        <td class="cell-border">{{ ($positionSpanned !== true) ? $positionName : '' }}</td>
                        @php
                            $positionSpanned = true;
                        @endphp
                        
                        <td class="cell-border">{{ $member->name }}</td>
                        <td class="cell-right cell-border">@formatNumber($total = $member->total_user, 0)</td>

                        @php
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