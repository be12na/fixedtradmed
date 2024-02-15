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
    <input type="hidden" class="input-branch" name="branch_id" id="branch-central" value="{{ BRANCH_CENTRAL }}" data-zone="{{ strtolower(Arr::get(BRANCH_ZONES, $myBranch->wilayah ?? 0)) }}" disabled>
    <div class="d-block" id="alert-container">
        @include('partials.alert')
    </div>
    <div class="row g-2 mb-2">
        <div class="col-sm-6 col-lg-4">
            <label class="d-block required">Tanggal</label>
            <div class="input-group">
                <input type="text" class="form-control bg-white" name="purchase_date" id="purchase-date" autocomplete="off" value="{{ $defaultDateValue }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
                <label for="purchase-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        {{-- <div class="col-sm-6 col-lg-4" id="branch-container">
            <label class="d-block">Cabang</label>
            <select class="form-select input-branch" name="branch_id" id="branch-id" disabled>
                <option value="" data-zone="">-- Pilih Cabang --</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" data-zone="{{ $branch->zone_id }}" @optionSelected($branch->id, $myBranch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </div> --}}
    </div>
    <div class="d-block mb-2 p-2 p-md-3 border rounded-3">
        <div class="fw-bold mb-3">Data Customer</div>
        <div class="row g-2 mb-2">
            {{-- <div class="col-md-6">
                <label class="d-block">No. Identitas</label>
                <input type="text" class="form-control" name="customer_identity" autocomplete="off">
            </div> --}}
            <div class="col-md-12">
                <label class="d-block required">Nama Lengkap Penerima</label>
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
                <thead class="bg-gradient-brighten small">
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
                </tbody>
                <tfoot class="small">
                    <tr>
                        <td rowspan="4" class="align-top border-bottom-0">
                            <button type="button" class="btn btn-sm btn-primary" id="add-purchase-item" style="width:100px;">
                                <i class="fa fa-plus me-1"></i>Tambah
                            </button>
                        </td>
                        <td></td>
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

    const foundationPersen = {{ $foundationPersen }};
    // const tv2 = '{{ DATE_V2 }}';

    // function formatDate(date) {
    //     var month = '' + (date.getMonth() + 1),
    //         day = '' + date.getDate(),
    //         year = date.getFullYear();

    //     if (month.length < 2) 
    //         month = '0' + month;
    //     if (day.length < 2) 
    //         day = '0' + day;

    //     return [year, month, day].join('-');
    // }
    
    function reSummary()
    {
        totalPurchase.html(0);

        const bzone = 'price';
        const items = $('.item-row');

        let total_salse = 0;
        let total_profit_crew = 0;
        let total_foundation = 0;
        let total_profit = 0;

        if (items.length > 0) {
            $.each(items, function(k, productItem) {
                const item = $(productItem);
                const itemProduct = item.find('.select-product').find(':selected');
                console.log(itemProduct);
                const itemPrice = item.find('.item-price');
                const itemQTY = item.find('.sale-item-qty').val();
                const itemAmount = item.find('.item-amount');
                
                let price = itemProduct.data(bzone);
                let itemCrew = itemProduct.data('profit-crew');

                price = (undefined != price) ? parseInt(price) : 0;
                itemCrew = (undefined != itemCrew) ? parseFloat(itemCrew) : 0;

                const amount = price * itemQTY;
                const itemProfitCrew = Math.floor(amount * itemCrew / 100);
                const itemFoundation = Math.floor(foundationPersen / 100 * itemProfitCrew);

                itemPrice.html($.number(price, 0, ',', '.'));
                itemAmount.html($.number(amount, 0, ',', '.'));

                total_salse = total_salse + amount;
                total_profit_crew = total_profit_crew + itemProfitCrew;
                total_foundation = total_foundation + itemFoundation;
            });
        }

        total_profit = total_salse - total_profit_crew - total_foundation;

        totalPurchase.html($.number(total_salse, 0, ',', '.'));
    }

    function removeItemProduct(obj)
    {
        obj.closest('tr').remove();
        reSummary();
    }

    $(function() {
        totalPurchase = $('#total-purchase');

        const datePurchase = $('#purchase-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        });
        // .on('changeDate', function(d) {
        //     const dt = formatDate(datePurchase.datepicker('getDate'));
        //     const iv2 = (dt >= tv2);

        //     $('.input-branch').each(function(ib) {
        //         const b = $(this);
        //         if (b.is('SELECT')) {
        //             b.attr({'disabled': !iv2});
        //         } else {
        //             b.attr({'disabled': iv2});
        //         }
        //     });

        //     if (iv2) {
        //         $('#branch-container').removeClass('d-none');
        //     } else {
        //         $('#branch-container').addClass('d-none');
        //     }

        //     reSummary();
        // });

        $('#add-purchase-item').on('click', function(e) {
            const me = $(this);
            // const branchId = $('.input-branch:not(:disabled)').val();
            const purchaseDate = datePurchase.val();

            // if (branchId) {
                me.attr('disabled', 'disabled');
                $.get({
                    url: '{{ route("mitra.purchase.createItem") }}',
                    data: {
                        // branch_id: branchId,
                        // date: purchaseDate,
                        mode: 'new'
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
            // } else {
            //     alert('Silahkan pilih cabang terlebih dahulu.');
            // }
        });

        $(document).on('click', '.btn-remove-item', function() {
            removeItemProduct($(this));
        });

        $(document).on('change', $('.select-product, .sale_count, select#branch-id'), function(e) {
            reSummary();
        });

        select_daerah = $('#daerah-id');
        select_daerah.select2({
            theme: 'classic',
            placeholder: '-- Pilih Daerah --',
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

        // datePurchase.trigger('changeDate');
    });
</script>
@endpush
