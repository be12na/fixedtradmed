@extends('layouts.app-mitra')

@php
    $indexRoute = route("mitra.purchase.index");

    $defaultDateValue = '';
    $endDate = '0 00 0000';

    if (!empty($dateRange)) {
        $startDate = formatFullDate($dateRange[0]);
        if (count($dateRange) > 1) {
            $endDate = $defaultDateValue = formatFullDate($dateRange[1]);
        }
    }
@endphp

@section('bodyClass', 'select2-40')

@section('content')
<form class="d-block" method="POST" action="{{ route('mitra.purchase.store') }}" id="myForm" data-alert-container="#alert-container">
    @csrf
    @if (!$isMitraPremium)
    <input type="hidden" class="input-branch" name="branch_id" id="branch-id" value="{{ BRANCH_CENTRAL }}" data-zone="{{ $branch->zone->id }}">
    @endif
    <div class="d-block" id="alert-container">
        @include('partials.alert')
    </div>
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Tanggal</label>
            {{-- <div class="input-group">
                <input type="text" class="form-control bg-white" name="purchase_date" id="purchase-date" autocomplete="off" value="{{ $defaultDateValue }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
                <label for="purchase-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div> --}}
            <input type="text" class="form-control bg-white" name="purchase_date" id="purchase-date" value="{{ $defaultDateValue }}" readonly>
        </div>
        @if ($isMitraPremium)
        <div class="col-sm-6 col-lg-4" id="branch-container">
            <label class="d-block">Cabang</label>
            <select class="form-select input-branch" name="branch_id" id="branch-id" autocomplete="off">
                <option value="" data-zone="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-zone="{{ $branch->zone_id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
    </div>
    <div class="d-block mb-2 p-2 p-md-3 border rounded-3">
        <div class="fw-bold mb-3">Data Customer</div>
        <div class="row g-2 mb-2">
            <div class="col-md-6">
                <label class="d-block">No. Identitas</label>
                <input type="text" class="form-control" name="customer_identity" autocomplete="off">
            </div>
            <div class="col-md-6">
                <label class="d-block required">Nama Lengkap Customer</label>
                <input type="text" class="form-control" name="customer_name" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Alamat</label>
                <input type="text" class="form-control" name="customer_address" autocomplete="off" required>
            </div>
            <div class="col-12">
                <label class="d-block required">Pilih Kelurahan - Kecamatan - Kab.Kota</label>
                <select class="form-select select2bs4 select2-custom" name="customer_village_id" id="daerah-id"></select>
                <div class="d-block" id="daerah-text"></div>
            </div>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                <label class="d-block">Kode Pos</label>
                <input type="text" class="form-control" name="customer_pos_code" autocomplete="off">
            </div>
            <div class="col-sm-8 col-md-6 col-lg-4">
                <label class="d-block">No. HP Aktif</label>
                <input type="text" class="form-control" name="customer_phone" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="d-block mb-2">
        <div class="d-block w-100 overflow-x-auto border">
            <table class="table table-sm table-striped table-nowrap mb-2">
                <thead class="bg-gradient-brighten small align-middle text-center">
                    <tr>
                        <th class="border-bottom" style="min-width:240px;" rowspan="2">Produk</th>
                        <th class="border-bottom border-start" style="width:180px;" rowspan="2">Jumlah</th>
                        <th class="border-bottom border-start" colspan="2">Harga</th>
                        <th class="border-bottom border-start" style="width:150px;" rowspan="2">Diskon</th>
                        <th class="border-bottom border-start" style="width:150px;" rowspan="2">Jumlah</th>
                        <th class="border-bottom border-start" style="width:150px;" rowspan="2">Potongan</th>
                        <th class="border-bottom border-start" style="width:150px;" rowspan="2">Total</th>
                        <th class="border-bottom border-start" rowspan="2"></th>
                    </tr>
                    <tr>
                        <th class="border-bottom border-start" style="width:150px;">Normal</th>
                        <th class="border-bottom border-start" style="width:150px;">Promo</th>
                    </tr>
                </thead>
                <tbody id="table-items" class="small border-top-0">
                    <tr>
                        <td colspan="9" class="p-0"></td>
                    </tr>
                </tbody>
                <tfoot class="small">
                    <tr>
                        <td colspan="6" class="align-top border-bottom-0">
                            <button type="button" class="btn btn-sm btn-primary" id="add-purchase-item" style="width:100px;">
                                <i class="fa fa-plus me-1"></i>Tambah
                            </button>
                        </td>
                        <td class="fw-bold text-end">Total</td>
                        <td class="fw-bold text-end" id="total-purchase"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row g-2 mb-2">
        <div class="col-12">
            <label class="d-block">Catatan (kosongkan jika tidak ada)</label>
            <textarea name="mitra_note" class="form-control" maxlength="250"></textarea>
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
    let totalPurchase;

    function reSummary()
    {
        totalPurchase.html(0);

        const branch = $('.input-branch');
        const bzone = branch.data('zone');

        const items = $('.item-row');

        let total_salse = 0;
        
        if (items.length > 0) {
            $.each(items, function(k, v) {
                const row = $(v);
                const itemQTY = row.find('.sale-item-qty');

                const itemDiscount = row.find('.item-discount');
                const itemSpecialDiscount = row.find('.item-special-discount');
                const itemNormalPrice = row.find('.item-normal-price');
                const itemPromoPrice = row.find('.item-promo-price');
                const itemPrice = row.find('.item-price');
                const itemAmount = row.find('.item-amount');

                if (itemQTY.length) {
                    const qty = itemQTY.val();
                    const isQtySelect = itemQTY.is('SELECT');
                    let discount;
                    const specialDiscount = itemQTY.data('special-discount') || 0;

                    if (isQtySelect) {
                        discount = $(':selected', itemQTY).data('discount') || 0;
                    } else {
                        discount = 0;
                        if (itemQTY.data('discount')) {
                            strDiscount = itemQTY.data('discount').split(',');
                            $.each(strDiscount, function(k, v) {
                                var kd = v.split('|');
                                var k = parseInt(kd[0].trim());
                                if (k <= qty) {
                                    discount = parseInt(kd[1].trim());
                                }
                            });
                        }

                    }
                    
                    const normalPrice = (qty > 0) ? (itemQTY.data('normal-price') || 0) : 0;
                    const promoPrice = (qty > 0) ? (itemQTY.data('promo-price') || 0) : 0;

                    let price = (promoPrice > 0) ? promoPrice : normalPrice;

                    if (price > 0) {
                        price = price - discount;
                    }

                    const totalPrice = price * qty;
                    const discountAmount = discount * qty;
                    const specialDiscountAmount = Math.floor(totalPrice * specialDiscount / 100);
                    const amount = totalPrice - specialDiscountAmount;

                    itemNormalPrice.html($.number(normalPrice, 0, ',', '.'));
                    itemPromoPrice.html($.number(promoPrice, 0, ',', '.'));
                    itemPrice.html($.number(totalPrice, 0, ',', '.'));
                    itemDiscount.html($.number(discount, 0, ',', '.'));
                    itemSpecialDiscount.html($.number(specialDiscountAmount, 0, ',', '.'));
                    itemAmount.html($.number(amount, 0, ',', '.'));
                    total_salse = total_salse + amount;
                } else {
                    itemNormalPrice.html(0);
                    itemPromoPrice.html(0);
                    itemPrice.html(0);
                    itemDiscount.html(0);
                    itemSpecialDiscount.html(0);
                    itemAmount.html(0);
                }
            });
        }

        totalPurchase.html($.number(total_salse, 0, ',', '.'));
    }

    function removeItemProduct(obj)
    {
        obj.closest('tr').remove();
        reSummary();
    }

    $(function() {
        totalPurchase = $('#total-purchase');
        
        const datePurchase = $('#purchase-date');
        
        $('#add-purchase-item').on('click', function(e) {
            const me = $(this);
            const purchaseDate = datePurchase.val();
            const branchId = $('.input-branch').val();

            if (branchId) {
                me.prop('disabled', true);
                $.get({
                    url: '{{ route("mitra.purchase.createItem") }}',
                    data: {
                        branch_id: branchId,
                        date: purchaseDate,
                        mode: 'new'
                    }
                }).done(function(respon) {
                    if (respon && respon != '') {
                        $('#table-items').append(respon);
                    } else {
                        alert('Produk tidak tersedia');
                    }
                }).always(function(respon) {
                    me.prop('disabled', false);
                    reSummary();
                });
            } else {
                alert('Silahkan pilih cabang terlebih dahulu.');
            }
        });

        $(document).on('click', '.btn-remove-item', function() {
            removeItemProduct($(this));
        });

        $(document).on('change', '.sale-item-qty, #branch-id', function(e) {
            reSummary();
        });

        $(document).on('change', '.select-product', function(e) {
            const url = '{{ route("mitra.purchase.productQty") }}';
            const me = $(this);
            const branchId = $('.input-branch').val();

            const data = {
                product_id: me.val(),
                branch_id: branchId,
                date: datePurchase.val(),
            };

            const boxQty = me.closest('tr').find('.box-qty');
            boxQty.empty();

            $.get({
                url: url,
                data: data
            }).done(function(respon) {
                boxQty.html(respon);
            }).always(function(respon) {
                reSummary();
            });
        });

        select_daerah = $('#daerah-id');
        select_daerah.select2({
            theme: 'classic',
            'placeholder': '-- Pilih Daerah --',
            ajax: {
                url: function(params) {
                    params.current = select_daerah.val();
                    
                    return '{{ route("selectRegion") }}';
                },
                data: function (params) {
                    let dt = {
                        search: params.term,
                        current: params.current
                    };

                    return dt;
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        }).on('select2:select', function (e) {
            const data = e.params.data;
            const txt = $('#daerah-text');

            txt.html(data.text);

            if (data.text != '') {
                txt.addClass('mt-2');
            } else {
                txt.removeClass('mt-2');
            }
        });

        reSummary();
    });
</script>
@endpush
