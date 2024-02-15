@extends('layouts.app-mitra')

@section('content')
@include('partials.alert')
<div class="row g-2 mb-2" id="bonus-filter">
    <div class="col-md-5 col-lg-4 d-flex">
        <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
            <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total</div>
            <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1" id="total-bonusan">0</div>
        </div>
    </div>
    <div class="col-md-7 col-lg-8 d-flex">
        @include('main.bonuses.menu.date-filter')
    </div>
</div>
<table class="table table-sm table-striped table-nowrap table-hover fs-auto" id="table">
    <thead class="bg-gradient-brighten bg-white">
        <tr class="text-center">
            <th>Tanggal</th>
            @if (!$isShoppingPoint)
                <th class="border-start">Sumber</th>
            @endif
            <th class="border-start">No. Transaksi</th>
            @if ($isShoppingPoint)
                <th class="border-start">Produk</th>
                <th class="border-start">Jumlah</th>
            @else
                <th class="border-start">Paket</th>
            @endif
            <th class="border-start">Poin</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
@endsection

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
    let table, filterCheck;

    function formatNumber(value)
    {
        return $.number(value, 0, ',', '.');
    }

    function setTotal()
    {
        const totalan = $('#total-bonusan');

        data = {
            start_date: $('#start-date').val(),
            end_date: $('#end-date').val(),
        };

        $.ajax({
            url: '{{ $totalUrl }}',
            type: 'GET',
            dataType: 'JSON',
            data: data
        }).done(function(respon) {
            totalan.empty().html(formatNumber(respon.total));
        }).fail(function(respon) {
            totalan.empty().html(0);
        });
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
                url: '{{ $dataUrl }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                }
            },
            deferLoading: 50,
            info: true,
            searching: false,
            pagingType: datatablePagingType,
            lengthMenu: [10, 25, 50],
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'desc']],
            columns: [
                {data: 'point_date'},
                @if (!$isShoppingPoint)
                    {data: 'from_member_name', orderable: false},
                @endif
                {data: 'purchase_no', orderable: false},
                @if ($isShoppingPoint)
                    {data: 'product_name', orderable: false},
                    {data: 'product_qty', orderable: false, className: 'dt-body-center', render: function(data, type, row) {
                        return formatNumber(data);
                    }},
                @else
                    {data: 'package_name', orderable: false},
                @endif
                {data: 'point', orderable: false, className: 'dt-body-center', render: function(data, type, row) {
                    return formatNumber(data);
                }},
            ],
        }).on( 'draw.dt', function () {
            setTotal();
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        $('#bonus-filter').prependTo(dtWrapper);

        customizeDatatable();

        const dtControlButton = $('.datatable-controls', dtWrapper);
        const btnLast = $('.datatable-controls > :first-child > :last-child', dtWrapper);
        btnLast.addClass('me-2').after($('#table_length'));

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        });

        $('.bs-date, #internal-check').on('change', function() {
            refreshTable();
        });

        refreshTable();
    });
</script>

@endpush