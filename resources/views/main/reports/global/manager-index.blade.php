@extends('layouts.app-main')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 border-bottom pb-2" id="filter-container">
    <div class="row g-2">
        @include('partials.filter-date', ['dateRange' => $dateRange])
        @include('partials.filter-branch', ['branches' => $branches, 'currentBranchId' => $currentBranchId])
        @include('partials.filter-team', [
            'teamLabel' => 'Manager', 
            'selectId' => 'manager-id',
            'defaultOption' => !empty($currentManager) ? ['id' => $currentManager->id, 'text' => $currentManager->name . ' (' . $currentManager->username . ')'] : [],
        ])
    </div>
</div>
<div class="d-block fs-auto">
    <table class="table table-sm table-nowrap table-striped table-hover" id="table">
        <thead class="bg-gradient-brighten bg-white text-center align-middle">
            <tr>
                <th rowspan="2">Manager</th>
                <th class="border-start" rowspan="2">Cabang</th>
                <th class="border-start" rowspan="2">Produk</th>
                <th class="border-start" colspan="2">Total Qty</th>
                <th class="border-start" rowspan="2">Total Omzet</th>
            </tr>
            <tr>
                <th class="border-start">Box</th>
                <th class="border-start">Pcs</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel" data-download-url="{{ route('main.reports.global.manager.download', ['exportFormat' => 'xlsx']) }}">
    <i class="fa-solid fa-file-excel me-1 text-primary"></i>Excel
</button>
<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download PDF" data-download-url="{{ route('main.reports.global.manager.download', ['exportFormat' => 'pdf']) }}">
    <i class="fa-solid fa-file-pdf me-1 text-primary"></i>PDF
</button>
@endsection

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
    #table > tbody > tr.group > * {
        font-weight: 600;
        background-color: var(--bs-gray-400) !important;
        box-shadow: none !important;
    }
    .table {
        width: 100% !important;
    }
</style>
@endpush

@push('scripts')

@php
    $myDtButtons = [];
    $myDtButtons[] = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => "refreshTable();"
    ];
@endphp

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'md',
])

<script>
    let table, select_branch, select_manager;

    function refreshTable()
    {
        table.ajax.reload();
    }

    function doExport(obj)
    {
        let url = obj.data('download-url') + '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&branch_id=' + select_branch.val() + '&manager_id=' + select_manager.val();

        window.open(url);
    }

    $(function() {
        select_branch = $('#branch-id');
        select_manager = $('#manager-id');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.reports.global.manager.datatable") }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.branch_id = select_branch.val();
                    d.manager_id = select_manager.val();

                    d.columns[1]['orderable'] = true;
                    d.order.push({column: 1, dir: 'asc'});
                    d.columns[2]['orderable'] = true;
                    d.order.push({column: 2, dir: 'asc'});
                }
            },
            deferLoading: 50,
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            sorting: false,
            order: [[0, 'asc']],
            columnDefs: [
                {searchable: false, orderable: false, targets: [1, 2, 3, 4, 5]},
            ],
            columns: [
                {data: 'manager_name'},
                {data: 'branch_name'},
                {data: 'product_name'},
                {data: 'qty_box', className: 'dt-body-right'},
                {data: 'qty_pcs', className: 'dt-body-right'},
                {data: 'total_omzet', className: 'dt-body-right'}
            ],
            // drawCallback: function (settings) {
            //     const api = this.api();
            //     const apiRows = api.rows({ page: 'current' });
            //     const rows = apiRows.nodes();
                
            //     apiRows.every(function ( rowIdx, tableLoop, rowLoop ) {
            //         const data = this.data();
            //         const details = JSON.parse(data.details);
            //         let gr = $(rows[rowIdx]).addClass('group');
            //         let bId = 0;

            //         for(let b = 0; b < details.length; b++) {
            //             let rb = gr.clone().removeClass('group');

            //             $('td:nth-child(1)', rb).html('');
                        
            //             if (bId != details[b].branch_id) {
            //                 $('td:nth-child(2)', rb).html(details[b].branch_name);
            //                 bId = details[b].branch_id;
            //             }

            //             $('td:nth-child(3)', rb).html(details[b].product_name);
            //             $('td:nth-child(4)', rb).html(details[b].html_qty_box);
            //             $('td:nth-child(5)', rb).html(details[b].html_qty_pcs);
            //             $('td:nth-child(6)', rb).html(details[b].html_total_omzet);
                        
            //             gr.after(rb);
            //             gr = rb;
            //         }
            //     });
            // },
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        $('#filter-container').prependTo(dtWrapper);
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
        });

        $('.bs-date').on('change', function() {
            refreshTable();
        });

        select_manager.select2({
            theme: 'classic',
            placeholder: '-- Semua --',
            allowClear: true,
            ajax: {
                url: function(params) {
                    params.current = select_manager.val();
                    params.branch = select_branch.val();
                    
                    return '{{ route("main.select2.managers") }}';
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
            select_manager.empty();
            refreshTable();
        });

        refreshTable();
    });
</script>
@endpush
