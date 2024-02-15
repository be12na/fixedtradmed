@extends('layouts.app-member')

@section('bodyClass', 'select2-40')

@php
    $indexRoute = route("main.sales.index");
    $startDate = formatFullDate($dateRange[0]);
    $endDate = formatFullDate($dateRange[1]);
@endphp

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>
<form class="d-block" method="POST" action="{{ route('main.sales.update', ['branchSaleModifiable' => $data->id]) }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Tanggal</label>
            <div class="input-group">
                <input type="text" class="form-control bg-white" name="sale_date" id="sale-date" autocomplete="off" value="{{ $data->sale_date }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
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
                    <option value="{{ $branch->id }}" data-zone="{{ strtolower(Arr::get(BRANCH_ZONES, $branch->wilayah, '')) }}" @optionSelected($branch->id, $data->branch_id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label class="d-block required">Salesman</label>
            <select class="form-select" name="salesman_id" id="salesman-id">
                <option value="{{ $data->salesman_id }}">{{ $data->salesman->name }} ({{ $data->salesman->internal_position_code }})</option>
            </select>
        </div>
    </div>
    <div class="d-block mb-2">
        <div class="d-block w-100 overflow-x-auto border">
            <table class="table table-sm table-striped table-nowrap mb-2">
                <thead class="bg-gradient-brighten bg-white small">
                    <tr>
                        <th class="border-bottom" style="min-width:240px;">Produk</th>
                        <th class="border-bottom border-start" style="width:180px;">Jumlah</th>
                        <th class="border-bottom border-start text-end" style="width:180px;">Harga</th>
                        <th class="border-bottom border-start text-end" style="width:180px;">Total</th>
                        <th class="border-bottom border-start"></th>
                    </tr>
                </thead>
                <tbody id="table-items" class="small border-top-0">
                    <tr>
                        <td colspan="5" class="p-0"></td>
                    </tr>
                    @foreach ($data->products as $saleItem)
                        @include('main.sales.sale-items', [
                            'itemsProduct' => $itemsProduct,
                            'saleItem' => $saleItem
                        ])
                    @endforeach
                </tbody>
                <tfoot class="small">
                    <tr>
                        <td rowspan="4" class="align-top border-bottom-0">
                            <button type="button" class="btn btn-sm btn-primary" id="add-sale-item" style="width:100px;">
                                <i class="fa fa-plus me-1"></i>Tambah
                            </button>
                        </td>
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
        {{-- <div class="col-auto col-sm-6 col-md-4">
            <label class="d-block">Saving</label>
            <input name="savings" type="number" step="1" min="0" class="form-control" value="0">
        </div> --}}
        <div class="col-12">
            <label>Catatan</label>
            <textarea name="salesman_note" class="form-control" maxlength="250">{{ $data->salesman_note }}</textarea>
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

    function reSummary()
    {
        totalSale.html(0);

        const branch = $('#branch-id');
        const bzone = branch.find(':selected').data('zone');
        const items = $('.item-row');

        let total_sale = 0;

        if (items.length > 0) {
            $.each(items, function(k, productItem) {
                const item = $(productItem);
                const itemProduct = item.find('.select-product').find(':selected');
                const itemPrice = item.find('.item-price');
                const itemQTY = item.find('.sale-item-qty').val();
                const itemAmount = item.find('.item-amount');
                
                let price = itemProduct.data('zone-' + bzone);
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
    
    
    $(function() {
        totalSale = $('#total-sale');
        select_salesman = $('#salesman-id');
        select_branch = $('#branch-id');

        $('#sale-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true,
            daysOfWeekDisabled: [0]
        });

        $('#add-sale-item').on('click', function(e) {
            const me = $(this);
            const branchId = select_branch.val();
            const saleDate = $('#sale-date').val();

            if (branchId) {
                me.attr('disabled', 'disabled');
                $.get({
                    url: '{{ route("main.sales.createItem") }}',
                    data: {
                        branch_id: branchId,
                        date: saleDate,
                        mode: 'edit'
                    }
                }).done(function(respon) {
                    if (respon && respon != '') {
                        $('#table-items').append(respon);
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
                    
                    return '{{ route("main.sales.crew") }}';
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

        $(document).on('change', $('.select-product, .sale_count'), function(e) {
            reSummary();
        });

        reSummary();
    });
</script>
@endpush
