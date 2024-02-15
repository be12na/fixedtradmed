@extends('layouts.app-member')

@section('bodyClass', 'select2-40')

@php
    $indexRoute = route("member.payment.index");
@endphp

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>
<form class="d-block fs-auto" method="POST" action="{{ route('member.payment.store') }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Tanggal</label>
            <input type="text" class="form-control" value="@formatFullDate(date('Y-m-d'), 'd F Y')" readonly>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Cabang</label>
            <select class="form-select" name="branch_id" id="branch-id">
                <option value="" data-zone="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-zone="{{ $branch->zone_id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="d-block mb-2">
        <div class="d-block w-100 overflow-x-auto border">
            <table class="table table-sm table-striped table-nowrap mb-2">
                <thead class="bg-gradient-brighten bg-white">
                    <tr>
                        <th class="border-bottom" style="min-width:240px;">
                            <span class="d-inline me-1">Produk</span><span class="d-inline fw-normal">(Satuan)</span>
                        </th>
                        <th class="border-bottom border-start" style="width:180px;">Jumlah</th>
                        <th class="border-bottom border-start" style="width:180px;">Satuan</th>
                        <th class="border-bottom border-start text-end" style="width:180px;">Harga</th>
                        <th class="border-bottom border-start text-end" style="width:180px;">Total</th>
                        <th class="border-bottom border-start"></th>
                    </tr>
                </thead>
                <tbody id="table-items" class="border-top-0"></tbody>
                <tfoot>
                    <tr>
                        <td rowspan="4" class="align-top border-bottom-0">
                            <button type="button" class="btn btn-sm btn-primary" id="add-payment-item" style="width:100px;">
                                <i class="fa fa-plus me-1"></i>Tambah
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="fw-bold text-end">Total</td>
                        <td class="fw-bold text-end" id="total-payment"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="d-block">
        <button type="button" class="btn btn-sm btn-primary" id="form-confirm">
            <i class="fa-solid fa-save me-1"></i>
            Simpan
        </button>
        <button type="button" class="btn btn-sm btn-warning" onclick="window.location.replace('{{ $indexRoute }}');">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
</form>
@endsection

@push('includeContent')
@include('partials.modals.modal-lg', [
    'bsModalId' => 'my-modal',
    'scrollable' => true
])
@endpush

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker-1.9.0/css/bootstrap-datepicker.standalone.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
@endpush

@push('styles')
<style>
    .table > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

@push('scripts')
<script>
    let totalPayment, select_branch;

    function doSubmit()
    {
        $('#my-modal').modal('hide');
        $('#myForm').submit();
    }
    
    function reSummary()
    {
        totalPayment.html(0);

        const branch = $('#branch-id');
        const bzone = branch.find(':selected').data('zone');
        const rowItems = $('.item-row');
        let total_payment = 0;

        if (rowItems.length > 0) {
            $.each(rowItems, function(k, rowItem) {
                const item = $(rowItem);
                const selectProduct = item.find('.select-product');
                const unitBoxId = selectProduct.data('unit-box');
                const unitPcsId = selectProduct.data('unit-pcs');
                const itemProduct = selectProduct.find(':selected');
                const ecer = (itemProduct.data('eceran') == 1);
                const itemPrice = item.find('.item-price');
                const itemQTY = item.find('.payment-item-qty').val();
                const itemAmount = item.find('.item-amount');
                
                let zoneName = 'zone-' + bzone;
                if (ecer) {
                    const unitSelect = item.find('.unit-select');
                    if (unitSelect.val() == unitPcsId) {
                        zoneName = 'eceran-' + zoneName;
                    }
                }
                let price = itemProduct.data(zoneName);

                price = (undefined != price) ? parseInt(price) : 0;
                
                const amount = price * itemQTY;

                itemPrice.html($.number(price, 0, ',', '.'));
                itemAmount.html($.number(amount, 0, ',', '.'));
                total_payment = total_payment + amount;
            });
        }

        totalPayment.html($.number(total_payment, 0, ',', '.'));
    }

    function removeItemProduct(obj)
    {
        obj.closest('tr').remove();
        reSummary();
    }

    function productChanged(obj)
    {
        let tr, selectProduct;

        if (obj.is('tr')) {
            tr = obj;
            selectProduct = tr.find('.select-product');
        } else {
            selectProduct = obj;
            tr = selectProduct.closest('tr');
        }

        const unitName = tr.find('.unit-name');
        const selectedUnitName = $(':selected', selectProduct).data('unit-name');
        unitName.html(selectedUnitName ? selectedUnitName : '-');
    }
    
    $(function() {
        totalPayment = $('#total-payment');
        select_branch = $('#branch-id');

        $('#add-payment-item').on('click', function(e) {
            const me = $(this);
            const branchId = select_branch.val();

            if (branchId) {
                me.attr('disabled', 'disabled');
                $.get({
                    url: '{{ route("member.payment.createItem") }}',
                    data: {
                        branch_id: branchId,
                        date: "{{ date('Ymd') }}",
                        mode: 'new'
                    }
                }).done(function(respon) {
                    if (respon && respon != '') {
                        const tr = $(respon);
                        $('#table-items').append(tr);
                        productChanged(tr);
                    } else {
                        alert('Produk tidak tersedia');
                    }
                }).always(function(respon) {
                    me.attr('disabled', false);
                    reSummary();
                });
            } else {
                alert('Silahkan pilih cabang terlebih dahulu.');
            }
        });

        $(document).on('click', '.btn-remove-item', function() {
            removeItemProduct($(this));
        });

        $(document).on('change', '.select-product, .payment-item-qty, .unit-select', function(e) {
            const me = $(this);
            if (me.hasClass('select-product')) {
                productChanged(me);
            }
            reSummary();
        });

        select_branch.on('change', function(e) {
            reSummary();
        }).change();

        $('#form-confirm').on('click', function(e) {
            const mdl = $('#my-modal');
            const data = $('#myForm').serialize();
            mdl.modal('show');
            const dlg = $('.modal-dialog', mdl).empty();
            $.get({
                url: '{{ route("member.payment.createConfirm") }}',
                data: data
            }).done(function(respon) {
                dlg.html(respon);
            }).fail(function(respon) {
                const content = '<div class="modal-content"><div class="modal-header py-2 px-3"><span class="fw-bold small">Error ' + respon.status + '</span><button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;"><i class="fa-solid fa-times"></i></button></div><div class="modal-body">' + respon.responseText + '</div>' +
                '<div class="modal-footer py-1"><button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal"><i class="fa-solid fa-times me-1"></i>Tutup</button></div></div>';
                dlg.html(content);
            });
        });
    });
</script>
@endpush