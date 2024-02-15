@extends('layouts.app-main')

@section('content')
@include('partials.alert')

@include('main.bonuses.menu.summary')
<div class="filter-container mb-2" id="bonus-filter">
    @include('main.bonuses.menu.date-filter')
</div>
<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-gray-100 small align-middle text-center">
        <tr>
            <th rowspan="3">Posisi</th>
            <th class="border-start border-gray-400" rowspan="3">Anggota</th>
            <th class="border-start border-gray-400" colspan="12">Produk QTY dan Bonus</th>
            <th class="border-start border-gray-400" rowspan="3">Total</th>
        </tr>
        <tr>
            <th class="border-start border-gray-400" colspan="3">Royalty</th>
            <th class="border-start border-gray-400" colspan="3">Override</th>
            <th class="border-start border-gray-400" colspan="3">Team</th>
            <th class="border-start border-gray-400" colspan="3">Penjualan</th>
        </tr>
        <tr>
            <th class="border-start border-gray-400">Box</th>
            <th class="border-start border-gray-400">Pcs</th>
            <th class="border-start border-gray-400">Bonus</th>
            <th class="border-start border-gray-400">Box</th>
            <th class="border-start border-gray-400">Pcs</th>
            <th class="border-start border-gray-400">Bonus</th>
            <th class="border-start border-gray-400">Box</th>
            <th class="border-start border-gray-400">Pcs</th>
            <th class="border-start border-gray-400">Bonus</th>
            <th class="border-start border-gray-400">Box</th>
            <th class="border-start border-gray-400">Pcs</th>
            <th class="border-start border-gray-400">Bonus</th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>

<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel" data-download-url="{{ route('main.bonus.summary.downloadSummary', ['summaryName' => 'total', 'exportFormat' => 'xlsx']) }}">
    <i class="fa-solid fa-file-excel me-1 text-primary"></i>Excel
</button>
<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download PDF" data-download-url="{{ route('main.bonus.summary.downloadSummary', ['summaryName' => 'total', 'exportFormat' => 'pdf']) }}">
    <i class="fa-solid fa-file-pdf me-1 text-primary"></i>PDF
</button>
@endsection

@push('styles')
<style>
    #table > thead > tr > :nth-child(1) {
        width: 60px !important;
    }
    #table > tbody > tr.group > * {
        font-weight: 600;
    }
    #table > tbody > tr.group > * {
        background-color: var(--bs-primary) !important;
        color: var(--bs-light);
        box-shadow: none !important;
    }
    #table > tbody > tr:not(.group) > td:nth-child(5),
    #table > tbody > tr:not(.group) > td:nth-child(8),
    #table > tbody > tr:not(.group) > td:nth-child(11),
    #table > tbody > tr:not(.group) > td:nth-child(14),
    #table > tbody > tr:not(.group) > td:nth-child(15) {
        font-weight: 600;
    }
    .table {
        width: 100% !important;
    }
</style>
@endpush

@php
    $myDtButtons = [
        [
            'id' => 'btn-refresh',
            'html' => '<i class="fa-solid fa-rotate"></i>',
            'title' => 'Refresh',
            'onclick' => "refreshTable();"
        ],
    ];
@endphp

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker-1.9.0/css/bootstrap-datepicker.standalone.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
@endpush

@push('scripts')

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'sm',
])

<script>
    let table;

    function formatNumber(value)
    {
        return $.number(value, 0, ',', '.');
    }
    
    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + formatNumber(value);
    }

    function refreshTable()
    {
        table.ajax.reload();
    }

    function doExport(obj)
    {
        let url = obj.data('download-url') + '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val();

        window.open(url);
    }

    function fixClassRows()
    {
        const rows = $('#table > tbody > tr');
        $.each(rows, function(k, v) {
            const m = (k + 1) % 2;
            const r = $(v);
            if (m != 0) {
                r.removeClass('even').addClass('odd');
            } else {
                r.removeClass('odd').addClass('even');
            }
        });
    }

    $(function() {
        const group1 = 0;
        const totalCol = 8;

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.bonus.summary.datatableSummary", ["summaryName" => "total"]) }}',
                data: function(d) {
                    const order = d.order[0];
                    if (order.column == 0) {
                        d.order.push({column: 1, dir: 'asc'});
                    } else if (order.column == 1) {
                        d.order.unshift({column: 0, dir: 'asc'});
                    }

                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                }
            },
            deferLoading: 50,
            info: true,
            search: {
                return: true
            },
            pagingType: datatablePagingType,
            lengthMenu: [10, 25, 50],
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'asc']],
            columns: [
                {data: 'position_id', searchable: false},
                {data: 'name'},
                {data: 'box_royalty', className: 'dt-body-center', visible: false, render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'pcs_royalty', className: 'dt-body-center', visible: false, render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'royalty', className: 'dt-body-right', visible: false, render: function(data, type, row) {
                    return formatCurrency(data);
                }, searchable: false, orderable: false},
                {data: 'box_override', className: 'dt-body-center', visible: false, render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'pcs_override', className: 'dt-body-center', visible: false, render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'override', className: 'dt-body-right', visible: false, render: function(data, type, row) {
                    return formatCurrency(data);
                }, searchable: false, orderable: false},
                {data: 'box_team', className: 'dt-body-center', render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'pcs_team', className: 'dt-body-center', render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'team', className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }, searchable: false, orderable: false},
                {data: 'box_sale', className: 'dt-body-center', render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'pcs_sale', className: 'dt-body-center', render: function(data, type, row) {
                    return formatNumber(data);
                }, searchable: false, orderable: false},
                {data: 'sale', className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }, searchable: false, orderable: false},
                {data: 'total_bonus', className: 'dt-body-right fw-bold', render: function(data, type, row) {
                    return formatCurrency(data);
                }, searchable: false, orderable: false},
            ],
            drawCallback: function (settings) {
                const api = this.api();
                const apiRows = api.rows({ page: 'current' });
                const rows = apiRows.nodes();
                let g1 = null;
                let total1 = {};

                apiRows.every(function ( rowIdx, tableLoop, rowLoop ) {
                    const data = this.data();
                    const keyName1 = data.position_name.replaceAll(' ', '').toLowerCase();

                    const row = $(rows).eq(rowIdx);

                    if (data.position_id != g1) {
                        row.before('<tr class="group"><td colspan="' + totalCol + '">' + data.position_name + '</td><td id="group1-' + keyName1 +'" class="text-end fw-bold"></td></tr>');
                        
                        total1[keyName1] = 0;
                        g1 = data.position_id;
                    }

                    total1[keyName1] = total1[keyName1] + parseInt(data.total_bonus);

                    $('td:nth-child(1)', row).html('');
                });

                $.each(total1, function(k, v) {
                    $('#group1-' + k).html(formatCurrency(v));
                });
            },
        }).on( 'draw.dt', function () {
            fixClassRows();
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        $('#bonus-filter').prependTo(dtWrapper);
        customizeDatatable();

        $('.datatable-controls > :first-child > :first-child', dtWrapper).addClass('me-2')
            .find('.dt-buttons')
            .append($('.btn-exports').removeClass('d-none'));

        $('.btn-exports').on('click', function(e) {
            doExport($(this));
        });

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        }).on('change', function() {
            refreshTable();
        });

        refreshTable();
    });
</script>

@endpush
