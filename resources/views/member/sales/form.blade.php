@extends('layouts.app-member')

@section('bodyClass', 'select2-40')

@php
    $indexRoute = route("member.sale.index");
    $todayIndex = $dateRange[1]->dayOfWeek;
    $defaultDateValue = formatFullDate((clone $dateRange[1])->subDays(($todayIndex == 1) ? 2 : 1));
    $startDate = formatFullDate($dateRange[0]);
    $endDate = formatFullDate($dateRange[1]);
@endphp

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>
<form class="d-block fs-auto" method="POST" action="{{ route('member.sale.store') }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Tanggal</label>
            <div class="input-group">
                <input type="text" class="form-control bg-white" name="sale_date" id="sale-date" autocomplete="off" value="{{ $defaultDateValue }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
                <label for="sale-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Cabang</label>
            <select class="form-select" name="branch_id" id="branch-id">
                <option value="" data-zone="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                {{-- data-zone-v1="{{ strtolower(Arr::get(BRANCH_ZONES, $branch->wilayah, '')) }}" --}}
                    <option value="{{ $branch->id }}" data-zone="{{ $branch->zone_id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label class="d-block required">Salesman</label>
            <select class="form-select" name="salesman_id" id="salesman-id"></select>
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
                            <button type="button" class="btn btn-sm btn-primary" id="add-sale-item" style="width:100px;">
                                <i class="fa fa-plus me-1"></i>Tambah
                            </button>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="fw-bold text-end">Total</td>
                        <td class="fw-bold text-end" id="total-sale"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-12">
            <label>Catatan</label>
            <textarea name="salesman_note" class="form-control" maxlength="250"></textarea>
        </div>
    </div>
    <div class="d-block">
        <button type="submit" class="btn btn-sm btn-primary">
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
    let totalSale, select_branch, select_salesman;
    const foundationPersen = {{ $foundationPersen }};
    const tv2 = '{{ DATE_V2 }}';
    let isDateV2 = false;

    function formatDate(date) {
        var month = '' + (date.getMonth() + 1),
            day = '' + date.getDate(),
            year = date.getFullYear();

        if (month.length < 2) 
            month = '0' + month;
        if (day.length < 2) 
            day = '0' + day;

        return [year, month, day].join('-');
    }

    function reSummary()
    {
        totalSale.html(0);

        const branch = $('#branch-id');
        const bzone = branch.find(':selected').data(isDateV2 ? 'zone' : 'zone-v1');
        console.log(bzone);
        const rowItems = $('.item-row');
        let total_sale = 0;

        if (rowItems.length > 0) {
            $.each(rowItems, function(k, rowItem) {
                const item = $(rowItem);
                const selectProduct = item.find('.select-product');
                const unitBoxId = selectProduct.data('unit-box');
                const unitPcsId = selectProduct.data('unit-pcs');
                const itemProduct = selectProduct.find(':selected');
                const ecer = (itemProduct.data('eceran') == 1);
                const itemPrice = item.find('.item-price');
                const itemQTY = item.find('.sale-item-qty').val();
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
                total_sale = total_sale + amount;
            });
        }

        totalSale.html($.number(total_sale, 0, ',', '.'));
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

        const unitSelect = tr.find('.unit-select');
        const unitPcs = tr.find('.unit-pcs');
        const ecer = selectProduct.find(':selected').data('eceran');

        if (ecer == 1) {
            unitSelect.removeClass('d-none');
            unitPcs.addClass('d-none');
        } else {
            unitSelect.addClass('d-none');
            unitPcs.removeClass('d-none');
        }
    }
    
    $(function() {
        totalSale = $('#total-sale');
        select_salesman = $('#salesman-id');
        select_branch = $('#branch-id');

        const dateSale = $('#sale-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true,
            daysOfWeekDisabled: [0]
        }).on('changeDate', function(d) {
            const dt = formatDate(dateSale.datepicker('getDate'));
            isDateV2 = (dt >= tv2);
            reSummary();
        });

        $('#add-sale-item').on('click', function(e) {
            const me = $(this);
            const branchId = select_branch.val();
            const saleDate = $('#sale-date').val();

            if (branchId) {
                me.attr('disabled', 'disabled');
                $.get({
                    url: '{{ route("member.sale.createItem") }}',
                    data: {
                        branch_id: branchId,
                        date: saleDate,
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

        select_salesman.select2({
            placeholder: '-- Salesman --',
            allowClear: true,
            ajax: {
                url: function(params) {
                    params.current = select_salesman.val();
                    params.branch = select_branch.val();
                    
                    return '{{ route("member.sale.crew") }}';
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
        });

        select_branch.on('change', function(e) {
            select_salesman.empty();
            reSummary();
        });

        $(document).on('change', '.select-product, .sale-item-qty, .unit-select', function(e) {
            const me = $(this);
            if (me.hasClass('select-product')) {
                productChanged(me);
            }
            reSummary();
        });

        dateSale.trigger('changeDate');
    });
</script>
@endpush