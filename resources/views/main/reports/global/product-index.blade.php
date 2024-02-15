@extends('layouts.app-main')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 fs-auto border-bottom pb-2" id="sale-filter">
    <div class="row g-2">
        @include('partials.filter-date', ['dateRange' => $dateRange])
        @include('partials.filter-branch', ['branches' => $branches, 'currentBranchId' => $currentBranchId])
    </div>
</div>
<table class="table table-sm" id="table">
    <thead class="d-none">
        <tr class="text-center">
            <th>Cabang</th>
        </tr>
    </thead>
    <tbody class="border-top-0"></tbody>
</table>

@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

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
    let table;

    function refreshTable()
    {
        table.ajax.reload();
    }

    $(function() {
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.reports.global.product.datatable") }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.branch_id = $('#branch-id').val();
                }
            },
            deferLoading: 50,
            info: false,
            lengthChange: false,
            searching: false,
            pagingType: datatablePagingType,
            lengthMenu: [[1]],
            language: datatableLanguange,
            buttons: datatableButtons,
            sorting: false,
            order: [[0, 'asc']],
            columns: [
                {data: 'name', className: 'p-0'}
            ]
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        $('#sale-filter').prependTo(dtWrapper);
        customizeDatatable();
        
        const dtControlButton = $('.datatable-controls', dtWrapper);
        const dtLastControl = $('.datatable-controls > :last-child', dtWrapper);
        
        $('#buttons-1').addClass('me-2');
        const exportContainer = $('#buttons-1 .dt-buttons');
        const excelDownlod = $('<button/>').html('<span>Excel</span>').attr({class: 'dt-button buttons-html5', id: 'btn-download-excel', type: 'button'});
        exportContainer.append(excelDownlod);

        excelDownlod.on('click', function(e) {
            let url = '{{ route("main.reports.global.product.excel") }}?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&branch_id=' + $('#branch-id').val();

            window.open(url);
        });

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('.bs-date, #salesman-id').on('change', function() {
            refreshTable();
        });

        $('#branch-id').on('change', function(e) {
            const me = $(this);
            const branchId = me.val();

            $.get({
                url: '{{ route("main.sales.crew") }}',
                data: {branch_id: branchId}
            }).done(function(respon) {
                if (respon && respon != '') {
                    $('#salesman-id').html(respon);
                }
            }).always(function(respon) {
                me.attr('disabled', false);
                refreshTable();
            });
        }).change();
    });
</script>
@endpush
