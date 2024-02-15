@extends('layouts.app-main')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 fs-auto border-bottom pb-2" id="sale-filter">
    <div class="row g-2">
        @include('partials.filter-date', ['dateRange' => $dateRange])
        @include('partials.filter-branch', ['branches' => $branches, 'currentBranchId' => $currentBranchId])
        @include('partials.filter-team', [
            'teamLabel' => 'Salesman', 
            'selectId' => 'salesman-id',
            'defaultOption' => !empty($currentSalesman) ? ['id' => $currentSalesman->id, 'text' => $currentSalesman->name . ' (' . $currentSalesman->username . ')'] : [],
        ])
    </div>
</div>
<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Kode</th>
            <th class="border-start">Sales</th>
            <th class="border-start">Manager</th>
            <th class="border-start">Cabang</th>
            <th class="border-start">Total</th>
            <th class="border-start">Produk</th>
            <th class="border-start">Catatan</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
    <tfoot class="small fw-bold border-top-gray-500">
        <tr>
            <td class="text-end" colspan="5">Total</td>
            <td class="text-end"></td>
            <td></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

@php
    $canCreate = false;
    $myDtButtons = [];
    $myDtButtons[] = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => "refreshTable();"
    ];
@endphp

<div class="row gx-2 gx-md-3 justify-content-md-around order-first order-md-5 fw-bold mb-2 me-0 me-md-auto flex-md-fill small" id="content-summary">
    <div class="col-12 col-md-auto d-flex align-items-center">
        <div class="row gx-1 flex-nowrap flex-fill">
            <div class="col-4 col-sm-3 col-md-auto">Total</div>
            <div class="col-auto">:</div>
            <div class="col-auto" id="total-profit-all"></div>
        </div>
    </div>
</div>

@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker-1.9.0/css/bootstrap-datepicker.standalone.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
@endpush

@push('styles')
<style>
    .table.table-detail > tbody > tr > td,
    .table.table-detail > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

@push('scripts')

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'md',
])

<script>
    let table, select_branch, select_salesman;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function refreshTable()
    {
        table.ajax.reload();
    }

    $(function() {
        select_branch = $('#branch-id');
        select_salesman = $('#salesman-id');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.sales.datatable") }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.branch_id = select_branch.val();
                    d.salesman_id = select_salesman.val();
                }
            },
            deferLoading: 50,
            info: true,
            lengthChange: false,
            search: {
                return: true
            },
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'desc']],
            columns: [
                {data: 'tanggal', searchable: false},
                {data: 'kode'},
                {data: 'salesman_name'},
                {data: 'manager_name'},
                {data: 'branch_name'},
                {data: 'stotal_sale', searchable: false, className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
                {data: 'purchase_items', searchable: false, orderable: false},
                {data: 'salesman_note', searchable: false, orderable: false},
                {data: 'view', searchable: false, orderable: false, className: 'dt-body-center', exportable: false, printable:false},
            ],
            footerCallback: function(row, data, start, end, display) {
                const api = this.api();
                // profit
                let totalProfit = api.column( 5, { page: 'current'} ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $( api.column( 5 ).footer() ).html(formatCurrency(totalProfit));
                // ----
                // profit all
                let totalProfitAll = api.column( 5 ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                
                $('#total-profit-all').html(formatCurrency(totalProfitAll));
            }
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');

        $('#sale-filter').prependTo(dtWrapper);
        customizeDatatable();
        
        const dtControlButton = $('.datatable-controls', dtWrapper);
        const dtLastControl = $('.datatable-controls > :last-child', dtWrapper);
        
        dtLastControl.addClass('order-last').removeClass('flex-fill');
        dtControlButton.prepend($('#content-summary'));

        $('#buttons-1').addClass('me-2');
        const exportContainer = $('#buttons-1 .dt-buttons');
        const excelDownlod = $('<button/>').html('<span>Excel</span>').attr({class: 'dt-button buttons-html5', id: 'btn-download-excel', type: 'button'});
        exportContainer.append(excelDownlod);

        excelDownlod.on('click', function(e) {
            let url = '{{ route("main.sales.download.excel") }}?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&branch_id=' + $('#branch-id').val() + '&salesman_id=' + $('#salesman-id').val();

            window.open(url);
        });

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('.bs-date').on('change', function() {
            refreshTable();
        });

        select_salesman.select2({
            theme: 'classic',
            placeholder: '-- Semua --',
            allowClear: true,
            ajax: {
                url: function(params) {
                    params.current = select_salesman.val();
                    params.branch = select_branch.val();
                    
                    return '{{ route("main.select2.branchTeams") }}';
                },
                data: function (params) {
                    let dt = {
                        search: params.term,
                        current: params.current,
                        branch: params.branch
                    };

                    return dt;
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
            },
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: function(data) {
                return data.html;
            },
            templateSelection: function(data) {
                return data.text;
            }
        }).on('change', function (e) {
            refreshTable();
        });

        $('#branch-id').on('change', function(e) {
            select_salesman.empty();
            refreshTable();
        });

        refreshTable();
    });
</script>
@endpush
