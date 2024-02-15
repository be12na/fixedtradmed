@extends('layouts.app-main')

@section('content')
@include('partials.alert')
<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row g-2">
            <div class="col-md-2 d-flex align-items-center justify-content-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width:100px;">
            </div>
            <div class="col-md-6 text-center text-md-start">
                <div class="fw-bold">{{ $product->name }}</div>
                <div>{{ $product->category_name }}</div>
                <div class="d-none d-md-block">{!! $product->notes ?? '' !!}</div>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-center text-center">
                <div>
                    <div id="product-price" class="fw-bold fs-2"></div>
                    <div>Per - {{ $product->product_unit }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($product->prices as $price)
    <input type="hidden" id="zone-{{ $price->zone_id }}" value="{{ $price->mitra_price }}">
@endforeach

<div class="mb-2" id="discount-filter">
    <div class="input-group input-group-sm w-auto">
        <label for="zona" class="input-group-text">Zona</label>
        <select id="zone-id" class="form-select pe-4" autocomplete="off">
            @foreach ($zones as $zone)
                <option value="{{ $zone->id }}" @optionSelected($zone->id, $currentZoneId)>{{ $zone->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>QTY ({{ $product->product_unit }})</th>
            <th class="border-start">Diskon</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>

@php
    $canCreate = hasPermission('main.master.product.discount.create');
    $myDtButtons = [];
    $myDtButtons[] = [
        'class' => 'btn-href',
        'html' => 'Kembali',
        'data-href' => route('main.master.product.index'),
    ];
    if ($canCreate) {
        $myDtButtons[] = [
            'id' => 'btn-tambah',
            'html' => 'Tambah',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#my-modal',
            'data-modal-url' => route('main.master.product.discount.create', ['product' => $product->id]),
        ];
    }
    $myDtButtons[] = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => "refreshTable();"
    ];
@endphp
@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
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
    let table, select_zone;
    let create_url = '{{ route("main.master.product.discount.create", ["product" => $product->id]) }}';

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function refreshTable()
    {
        $('#btn-tambah').data('modal-url', create_url + '?zone_id=' + select_zone.val());
        table.ajax.reload();
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
        select_zone = $('#zone-id');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.master.product.discount.datatable", ["product" => $product->id]) }}',
                data: function(d) {
                    d.zone_id = select_zone.val();
                }
            },
            deferLoading: 50,
            info: false,
            lengthChange: false,
            searching: false,
            sort: false,
            paging: false,
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            columns: [
                {data: 'min_qty', className: 'dt-body-center'},
                {data: 'discount', className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
                {data: 'view', className: 'dt-body-center'},
            ],
            drawCallback: function (settings) {
                const api = this.api();
                const apiRows = api.rows({ page: 'current' });
                const rows = apiRows.nodes();
                let g1 = null;
                let total1 = {};

                apiRows.every(function ( rowIdx, tableLoop, rowLoop ) {
                    const data = this.data();
                    const row = $(rows).eq(rowIdx);

                    if (data.discount_category != g1) {
                        row.before('<tr class="group"><td colspan="3" class="fw-bold bg-primary text-light">' + data.discount_category_name + '</td></tr>');
                        
                        g1 = data.discount_category;
                    }
                });
            },
        }).on( 'draw.dt', function () {
            fixClassRows();
        });;

        customizeDatatable();
        
        const dtWrapper = $('#table_wrapper.dataTables_wrapper #buttons-2');
        dtWrapper.addClass('me-2').after($('#discount-filter'));
        
        select_zone.on('change', function(e) {
            const prc = $('#zone-' + $(this).val()).val();
            $('#product-price').html(formatCurrency(prc));
            refreshTable();
        }).change();
    });
</script>
@endpush
