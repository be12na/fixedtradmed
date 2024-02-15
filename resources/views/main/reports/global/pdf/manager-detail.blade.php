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
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }
        .table tbody td {
            vertical-align: top;
        }
        .cell-number {
            text-align: right;
        }
        .cell-border {
            border: 1px solid #212529;
        }
        .small {
            font-size: 8pt;
        }
        .info-upline {
            font-size: 9pt;
            font-style: italic;
        }
    </style>
</head>
<body>
    @php
        $tanggal = formatFullDate($startDate);
        if ($startDate->format('Y-m-d') != $endDate->format('Y-m-d')) {
            $tanggal .= ' s/d ' . formatFullDate($endDate);
        }
        $spanHeader = !empty($branch) ? 8 : 9;
    @endphp
    <table class="table">
        <thead>
            <tr>
                <th class="header-title" colspan="{{ $spanHeader }}">{{ $titleHeader }}</th>
            </tr>
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">{{ $tanggal }}</th>
            </tr>
            @if (!empty($branch))
            <tr>
                <th class="header-sub-title" colspan="{{ $spanHeader }}">Cabang {{ $branch->name }}</th>
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
                <th rowspan="2" class="cell-border">Nama</th>
                <th rowspan="2" class="cell-border">Posisi</th>
                <th rowspan="2" class="cell-border">Upline</th>
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
                $teamId = 0;
            @endphp
            @foreach ($rows->groupBy('team_id')->sortBy('team_name') as $team_id => $teams)
                @php
                    $branchId = 0;
                    $team = $teams->first();
                    $teamName = $team->team_name;
                    $positionName = $team->position_name;
                    $uplineInfo = "<div>{$team->upline_name}</div><div class=\"info-upline\">{$team->upline_position_name}</div>";
                @endphp
                @foreach ($teams->groupBy('branch_id')->sortBy('branch_name') as $branch_id => $branches)
                    @php
                        $branchName = $branches->first()->branch_name;
                    @endphp
                    @foreach ($branches->groupBy('product_id')->sortBy('product_name') as $products)
                        <tr>
                            <td class="cell-number cell-border">{{ ($teamId != $team_id) ? "{$noUrut}." : '' }}</td>
                            <td class="cell-border">{{ ($teamId != $team_id) ? $teamName : '' }}</td>
                            <td class="cell-border">{{ ($teamId != $team_id) ? $positionName : '' }}</td>
                            <td class="cell-border">{!! ($teamId != $team_id) ? $uplineInfo : '' !!}</td>
                            @if (empty($branch))
                                <td class="cell-border">{{ ($branchId != $branch_id) ? $branchName : '' }}</td>
                            @endif
                            <td class="cell-border">{{ $products->first()->product_name }}</td>
                            <td class="cell-number cell-border">@formatNumber($products->sum('qty_box'), 0)</td>
                            <td class="cell-number cell-border">@formatNumber($products->sum('qty_pcs'), 0)</td>
                            <td class="cell-number cell-border">@formatNumber($products->sum('total_omzet'), 0)</td>
                        </tr>
                    @endforeach
                    @php
                        $branchId = $branch_id;
                    @endphp
                @endforeach
                @php
                    $teamId = $team_id;
                    $noUrut++;
                @endphp
            @endforeach
        </tbody>
    </table>

    {{-- page number --}}
    <script type="text/php">
        if (isset($pdf)) {
            $text = "- {PAGE_NUM} -";
            $size = 10;
            $font = "{{ config('dompdf.defines.default_font') }}";
            $width = $fontMetrics->get_text_width($text, $font, $size) / 4;
            $x = ($pdf->get_width() - $width) / 2;
            $y = $pdf->get_height() - 20;
            $pdf->page_text($x, $y, $text, $font, $size);
        }
    </script>
</body>
</html>