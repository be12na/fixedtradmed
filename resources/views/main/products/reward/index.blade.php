@extends('layouts.app-main')

@section('content')
@include('partials.alert')
<div class="card mb-3">
    <div class="card-body p-2">
        <div class="row g-2">
            <div class="col-md-2 d-flex align-items-center justify-content-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="max-width:100px;">
            </div>
            <div class="col-md-10 text-center text-md-start">
                <div class="fw-bold">{{ $product->name }}</div>
                <div>{{ $product->category_name }}</div>
                <div class="d-none d-md-block">{!! $product->notes ?? '' !!}</div>
            </div>
        </div>
    </div>
</div>

<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>QTY ({{ $product->product_unit }})</th>
            <th class="border-start">Reward</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>

@php
    $canCreate = hasPermission('main.master.product.reward.create');
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
            'data-modal-url' => route('main.master.product.reward.create', ['product' => $product->id]),
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
    let table;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function refreshTable()
    {
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
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.master.product.reward.datatable", ["product" => $product->id]) }}'
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
                {data: 'total_qty', className: 'dt-body-center'},
                {data: 'reward_value'},
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

                    if (data.reward_type != g1) {
                        row.before('<tr class="group"><td colspan="3" class="fw-bold bg-primary text-light">' + data.reward_name + '</td></tr>');
                        
                        g1 = data.reward_type;
                    }
                });
            },
        }).on( 'draw.dt', function () {
            fixClassRows();
        });;

        customizeDatatable();
        
        refreshTable();
    });
</script>
@endpush
