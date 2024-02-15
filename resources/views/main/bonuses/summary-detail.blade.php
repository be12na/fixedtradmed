@extends('layouts.app-main')

@section('content')
@include('partials.alert')

{{-- <div class="row gy-2 gx-0 gx-lg-3"> --}}
    <input type="hidden" id="bonus-type" value="{{ $bonusType }}">
    {{-- <div class="col-lg-3 d-flex flex-column"> --}}
        @include('main.bonuses.menu.summary')
    {{-- </div> --}}
    {{-- <div class="col-lg-9"> --}}
        <div class="filter-container mb-2" id="bonus-filter">
            @include('main.bonuses.menu.date-filter')
        </div>
        <table class="table table-sm table-nowrap table-hover" id="table">
            <thead class="bg-gradient-brighten bg-white small align-middle text-center">
                <tr>
                    <th>Posisi</th>
                    <th class="border-start">Anggota</th>
                    <th class="border-start">Total</th>
                </tr>
            </thead>
            <tbody class="small"></tbody>
        </table>
    {{-- </div> --}}
{{-- </div> --}}

<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel" data-download-url="{{ route('main.bonus.summary.downloadSummary', ['summaryName' => $prefixName, 'exportFormat' => 'xlsx']) }}">
    <i class="fa-solid fa-file-excel me-1 text-primary"></i>Excel
</button>
<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download PDF" data-download-url="{{ route('main.bonus.summary.downloadSummary', ['summaryName' => $prefixName, 'exportFormat' => 'pdf']) }}">
    <i class="fa-solid fa-file-pdf me-1 text-primary"></i>PDF
</button>
@endsection

@push('styles')
<style>
    #table > thead > tr > :nth-child(1) {
        width: 60px !important;
    }
    #table > thead > tr > :nth-child(3) {
        width: 160px !important;
    }
    #table > tbody > tr.group > * {
        font-weight: 600;
        box-shadow: none !important;
    }
    #table > tbody > tr.group.group-1 > * {
        background-color: var(--bs-primary) !important;
        color: var(--bs-light);
    }
    #table > tbody > tr:not(.group) > :last-child {
        font-weight: 600;
        color: var(--bs-success);
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
    'datatableResponsive' => 'md',
])

<script>
    let table;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function refreshTable()
    {
        table.ajax.reload();
    }

    function doExport(obj)
    {
        let url = obj.data('download-url') + '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&bonus_type=' + $('#bonus-type').val();

        window.open(url);
    }

    $(function() {        
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.bonus.summary.datatableSummary", ["summaryName" => $prefixName]) }}',
                data: function(d) {
                    const order = d.order[0];
                    if (order.column == 0) {
                        d.order.push({column: 1, dir: 'asc'});
                        d.order.push({column: 2, dir: 'asc'});
                    } else if (order.column == 1) {
                        d.order.unshift({column: 0, dir: 'asc'});
                        d.order.push({column: 2, dir: 'asc'});
                    } else if (order.column == 2) {
                        d.order.unshift({column: 1, dir: 'asc'});
                        d.order.unshift({column: 0, dir: 'asc'});
                    }

                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.bonus_type = $('#bonus-type').val();
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
            columnDefs: [
                {orderable: false, targets: [2]}
            ],
            columns: [
                {data: 'position', searchable: false},
                {data: 'name'},
                {data: 'total_user', className: 'dt-body-right', searchable: false, render: function(data, type, row) {
                    return formatCurrency(data);
                }},
            ],
            drawCallback: function (settings) {
                const api = this.api();
                const apiRows = api.rows({ page: 'current' });
                const rows = apiRows.nodes();
                let g1 = null;
                let total1 = {};

                apiRows.every(function ( rowIdx, tableLoop, rowLoop ) {
                    const data = this.data();
                    if (data.position != g1) {
                        const keyName1 = data.position_code.replaceAll(' ', '').toLowerCase();
                        $(rows).eq(rowIdx).before('<tr class="group group-1"><td colspan="2">' + data.position_name + '</td><td id="group1-' + keyName1 +'" class="text-end fw-bold"></td></tr>');
                        
                        total1[keyName1] = data.total_position;
                        g1 = data.position;
                    }
                    $('td:nth-child(1)', $(rows).eq(rowIdx)).html('');
                });

                $.each(total1, function(k, v) {
                    $('#group1-' + k).html(formatCurrency(v));
                });
            },
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

        const dtControlButton = $('.datatable-controls', dtWrapper);
        const btnLast = $('.datatable-controls > :first-child > :last-child', dtWrapper);
        btnLast.addClass('me-2').after($('#table_length'));

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
