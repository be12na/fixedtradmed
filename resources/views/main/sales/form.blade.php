@extends('layouts.app-main')

@php
    $indexRoute = route('main.sales.index');

    $startDate = '0 00 0000';
    $endDate = '0 00 0000';

    if (!empty($dateRange)) {
        $startDate = formatFullDate($dateRange[0]);
        if (count($dateRange) > 1) $endDate = formatFullDate($dateRange[1]);
    }
@endphp

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>
<form class="d-block" method="POST" action="{{ route('main.sales.update', ['branchSale' => $data->id]) }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    {{-- <input type="hidden" id="branch_id" value="{{ $data->branch_id }}" data-zone="{{ strtolower(Arr::get(BRANCH_ZONES, $data->branch->wilayah, '')) }}"> --}}
    {{-- <input type="hidden" id="sale_date" value="{{ $data->getRawOriginal('sale_date') }}"> --}}
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            {{-- <label>Tanggal</label>
            <div class="form-control">{{ $data->sale_date }}</div> --}}
            <label class="d-block required">Tanggal</label>
            <div class="input-group">
                <input type="text" class="form-control bg-white" name="sale_date" id="sale-date" autocomplete="off" value="{{ $data->sale_date }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
                <label for="sale-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            {{-- <label>Cabang</label>
            <div class="form-control">{{ $data->branch->name }}</div> --}}
            <label class="d-block required">Cabang</label>
            <select class="form-select" name="branch_id" id="branch_id">
                <option value="" data-zone="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-zone="{{ strtolower(Arr::get(BRANCH_ZONES, $branch->wilayah, '')) }}" @optionSelected($branch->id, $data->branch_id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            {{-- <label>Salesman</label>
            <div class="form-control">{{ $data->salesman->name }}</div> --}}
            <label class="d-block required">Salesman</label>
            <select class="form-select" name="salesman_id" id="salesman_id">
                <option value="">-- Pilih Sales --</option>
                <option value="{{ $data->salesman_id }}" selected>{{ $data->salesman->name }}</option>
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
                    {{-- <tr>
                        <td></td>
                        <td class="fw-bold text-end">Profit Crew</td>
                        <td class="fw-bold text-end" id="profit-crew"></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td class="fw-bold text-end">Foundation</td>
                        <td class="border-bottom border-bottom-dark fw-bold text-end" id="foundation" style="--bd-bottom-width:2px;"></td>
                        <td></td>
                    </tr> --}}
                    {{-- <tr>
                        <td></td>
                        <td class="fw-bold text-end">Profit</td>
                        <td class="fw-bold text-end" id="total-profit"></td>
                        <td></td>
                    </tr> --}}
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-auto col-sm-6 col-md-4">
            <label class="d-block">Saving</label>
            <input name="savings" type="number" step="1" min="0" class="form-control" value="0">
        </div>
        <div class="col-12">
            <label>Catatan</label>
            {{-- <div class="form-control text-wrap">{{ $data->salesman_note ?? '-' }}</div> --}}
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
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
@endpush

@push('styles')
<style>
    .table > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

{{-- 
-
let profitCrew, foundation, totalProfit;
const foundationPersen = {{ $foundationPersen }};

--
profitCrew.html(0);
foundation.html(0);
totalProfit.html(0);

---
let total_profit_crew = 0;
let total_foundation = 0;
let total_profit = 0;

----
let itemCrew = itemProduct.data('profit-crew');
itemCrew = (undefined != itemCrew) ? parseFloat(itemCrew) : 0;
const itemProfitCrew = Math.floor(amount * itemCrew / 100);
const itemFoundation = Math.floor(foundationPersen / 100 * itemProfitCrew);

-----
total_profit_crew = total_profit_crew + itemProfitCrew;
total_foundation = total_foundation + itemFoundation;

------
total_profit = total_salse - total_profit_crew - total_foundation;

-------
profitCrew.html($.number(total_profit_crew, 0, ',', '.'));
foundation.html($.number(total_foundation, 0, ',', '.'));
totalProfit.html($.number(total_profit, 0, ',', '.'));

--------
profitCrew = $('#profit-crew');
foundation = $('#foundation');
totalProfit = $('#total-profit');
--}}

@push('scripts')
<script>
    // -
    let totalSale;

    function reSummary()
    {
        totalSale.html(0);
        // --

        const branch = $('#branch_id');
        const bzone = branch.find(':selected').data('zone');
        const items = $('.item-row');

        let total_salse = 0;
        // ---

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
                
                // ----

                itemPrice.html($.number(price, 0, ',', '.'));
                itemAmount.html($.number(amount, 0, ',', '.'));

                total_salse = total_salse + amount;
                // -----
            });
        }

        // ------

        totalSale.html($.number(total_salse, 0, ',', '.'));
        // -------
    }

    function removeItemProduct(obj)
    {
        obj.closest('tr').remove();
        reSummary();
    }
    
    
    $(function() {
        totalSale = $('#total-sale');
        // --------

        $('#sale-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true,
            daysOfWeekDisabled: [0]
        });

        $('#add-sale-item').on('click', function(e) {
            const me = $(this);
            const branchId = $('#branch_id').val();
            const saleDate = $('#sale_date').val();

            if (branchId) {
                me.attr('disabled', 'disabled');
                $.get({
                    url: '{{ route("main.sales.createItem") }}',
                    data: {
                        branch_id: branchId,
                        date: saleDate
                    }
                }).done(function(respon) {
                    if (respon && respon != '') {
                        $('#table-items').append(respon);
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

        $('#branch_id').on('change', function(e) {
            const me = $(this);
            const branchId = me.val();
            const salesman = $('#salesman_id');
            const salesmanId = salesman.val();
            salesman.empty();

            $.get({
                url: '{{ route("main.sales.crew") }}',
                data: {
                    branch_id: branchId,
                    manager_id: '{{ $data->manager_id }}',
                    salesman_id: salesmanId
                }
            }).done(function(respon) {
                if (respon && respon != '') {
                    salesman.html(respon);
                }
            }).always(function(respon) {
                reSummary();
            });
        });

        $(document).on('change', $('.select-product, .sale_count'), function(e) {
            reSummary();
        });

        $('#branch_id').change();
    });
</script>
@endpush
