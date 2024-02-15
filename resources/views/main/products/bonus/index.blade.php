@extends('layouts.app-main')

@php
    $canEdit = hasPermission('main.master.product.bonus.edit');
@endphp

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
<table class="table table-sm table-nowrap table-striped table-hover mb-2" id="table">
    <thead class="bg-gradient-brighten bg-white align-middle small text-center">
        <tr>
            <th rowspan="2">Zona</th>
            <th class="border-start" rowspan="2">Harga / {{ ucfirst(strtolower($product->product_unit)) }}</th>
            <th class="border-start" colspan="3">Bonus</th>
            @if ($canEdit)
                <th class="border-start" rowspan="2"></th>
            @endif
        </tr>
        <tr>
            <th class="border-start">Member Basic</th>
            <th class="border-start">Member Premium</th>
            <th class="border-start">Distributor</th>
        </tr>
    </thead>
    <tbody class="small">
        @if ($product->prices->isNotEmpty())
            @foreach ($product->prices->sortBy('zone_id') as $price)
                <tr>
                    <td class="text-center">{{ $price->zone->name }}</td>
                    <td class="text-end">@formatCurrency($price->mitra_price, 0, true, true)</td>
                    <td class="text-end">@formatCurrency($price->mitra_basic_bonus, 0, true, true)</td>
                    <td class="text-end">@formatCurrency($price->mitra_premium_bonus, 0, true, true)</td>
                    <td class="text-end">@formatCurrency($price->distributor_bonus, 0, true, true)</td>
                    @if ($canEdit)
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.master.product.bonus.edit', ['product' => $price->product_id, 'zone' => $price->zone_id]) }}">
                            <i class="fa-solid fa-pencil-alt mx-1"></i>
                        </button>
                    </td>
                    @endif
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="{{ $canEdit ? 6 : 5 }}" class="text-center">Harga belum ditentukan</td>
            </tr>
        @endif
    </tbody>
</table>

@php
    $myDtButtons = [];
    $myDtButtons[] = [
        'class' => 'btn-href',
        'html' => 'Kembali',
        'data-href' => route('main.master.product.index'),
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
    $(function() {
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: false,
            serverSide: false,
            info: false,
            lengthChange: false,
            searching: false,
            sort: false,
            paging: false,
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
        })

        customizeDatatable();        
    });
</script>
@endpush
