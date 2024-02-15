@extends('layouts.app-mitra')

@php
    $indexRoute = route("mitra.purchase.index");
@endphp

@section('content')
<form method="POST" action="{{ route('mitra.purchase.transfer.saveTransfer', ['mitraPurchase' => $purchase->id]) }}" id="myForm" enctype="multipart/form-data" data-alert-container="#alert-container">
    @csrf
    <div class="d-block" id="alert-container">
        @include('partials.alert')
    </div>
    <div class="d-block fs-auto mb-3">
        <div class="row g-2 mb-2">
            <div class="col-xl-4">
                <div class="card h-100">
                    <div class="card-header fw-bold">Pembelian</div>
                    <div class="card-body">
                        <div class="d-block mb-2">
                            <label class="d-block">Kode</label>
                            <input type="text" class="form-control bg-light" value="{{ $purchase->code }}" autocomplete="off" readonly="">
                        </div>
                        <div class="d-block mb-2">
                            <label class="d-block">Tanggal</label>
                            <input type="text" class="form-control bg-light" value="{{ $purchase->purchase_date }}" autocomplete="off" readonly="">
                        </div>
                        <div class="d-block mb-2">
                            <label class="d-block">Cabang</label>
                            <input type="text" class="form-control bg-light" value="{{ $purchase->branch->name }}" autocomplete="off" readonly="">
                        </div>
                        <div class="d-block mb-2">
                            <label class="d-block">Manager</label>
                            <input type="text" class="form-control bg-light" value="{{ optional($purchase->manager)->name ?? '-' }}" autocomplete="off" readonly="">
                        </div>
                        <div class="d-block">
                            <label class="d-block">Keterangan</label>
                            <div class="form-control text-wrap bg-light">{{ $purchase->mitra_note ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8">
                <div class="card h-100">
                    <div class="card-header fw-bold">Customer</div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-sm-6 col-lg-4">
                                <label class="d-block">Identitas</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_identity }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-6 col-lg-8">
                                <label class="d-block">Nama</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_name }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-12">
                                <label class="d-block">Alamat</label>
                                <div class="form-control text-wrap bg-light">{{ $purchase->customer_address ?? '-' }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block">Desa / Kelurahan</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_village }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block">Kecamatan</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_district }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block">Kota / Kabupaten</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_city }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block">Propinsi</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_province }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-4">
                                <label class="d-block">Kode Pos</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_pos_code }}" autocomplete="off" readonly="">
                            </div>
                            <div class="col-sm-8">
                                <label class="d-block">Handphone</label>
                                <input type="text" class="form-control bg-light" value="{{ $purchase->customer_phone }}" autocomplete="off" readonly="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-2">
            <div class="card-header fw-bold">Detail Pembelian</div>
            <div class="card-body">
                <div class="d-block border mb-3 overflow-x-auto">
                    <table class="table table-sm table-nowrap table-hover border-0 mb-2">
                        <thead class="bg-gradient-brighten bg-white small text-center align-middle">
                            <tr>
                                <th class="border-bottom" rowspan="2">Produk</th>
                                <th class="border-bottom border-start" rowspan="2">Jumlah</th>
                                <th class="border-bottom border-start" colspan="2">Harga</th>
                                <th class="border-bottom border-start" rowspan="2">Diskon</th>
                                <th class="border-bottom border-start" rowspan="2">Jumlah</th>
                                <th class="border-bottom border-start" rowspan="2">Potongan</th>
                                <th class="border-bottom border-start" rowspan="2">Total</th>

                                {{-- <th class="border-start">Harga</th>
                                <th class="border-start">QTY</th>
                                <th class="border-start">Total</th> --}}
                            </tr>
                            <tr>
                                <th class="border-bottom border-start">Normal</th>
                                <th class="border-bottom border-start">Promo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchase->products as $product)
                                <tr class="border-bottom">
                                    <td>{{ $product->product->name }}</td>
                                    <td class="text-end">@formatNumber($product->product_qty)</td>
                                    <td class="text-end">@formatCurrency($product->product_price, 0, true)</td>
                                    <td class="text-end">@formatCurrency($product->product_zone_price, 0, true)</td>
                                    <td class="text-end">@formatCurrency($product->discount, 0, true)</td>
                                    <td class="text-end">@formatCurrency(($product->actual_price - $product->discount) * $product->product_qty, 0, true)</td>
                                    <td class="text-end">@formatCurrency($product->coupon_discount, 0, true)</td>
                                    <td class="text-end">@formatCurrency($product->total_price, 0, true)</td>

                                    {{-- <td>{{ $product->product->name }}</td>
                                    <td class="text-end">@formatCurrency($product->product_price, 0, true)</td>
                                    <td class="text-end">@formatNumber($product->product_qty)</td>
                                    <td class="text-end">@formatCurrency($product->total_price, 0, true)</td> --}}
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot style="border-top-width: 2px;">
                            <tr>
                                <td class="fw-bold" colspan="7">Total</td>
                                <td class="text-end fw-bold">@formatCurrency($purchase->total_purchase, 0, true)</td>
                            </tr>
                            {{-- <tr>
                                <td colspan="3">
                                    <span class="d-inline-block fw-bold me-1">Diskon</span>
                                    @if ($purchase->discount_percent > 0)
                                    <span class="d-inline-block small">
                                        (@formatAutoNumber($purchase->discount_percent, false, 2)%)
                                    </span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold">
                                    @formatCurrency($purchase->discount_amount, 0, true)
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-wrap">
                                    <span class="d-inline-block fw-bold me-1">Total</span>
                                    <span class="d-inline-block fst-italic small">( Yang harus ditransfer )</span>
                                </td>
                                <td class="text-end fw-bold border-top border-top-dark">
                                    @formatCurrency($purchase->total_transfer, 0, true)
                                </td>
                            </tr> --}}
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header fw-bold">Metode Pembayaran</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-12">
                        <label>Keterangan</label>
                        <textarea name="transfer_note" class="form-control" maxlength="250"></textarea>
                    </div>
                    <div class="col-md-6 d-flex flex-column">
                        <label class="d-none d-md-block required">Jenis Pembayaran</label>
                        <div class="d-block flex-fill dropdown droptab dropup droptab-md">
                            <button type="button" class="d-block d-md-none w-100 btn btn-sm btn-primary" data-bs-toggle="dropdown" data-bs-target="dropdown-bank">Pilih Jenis Pembayaran</button>
                            <div class="dropdown-menu shadow-sm px-3" id="dropdown-bank">
                                @foreach ($banks as $name => $bankList)
                                    @if ($name == $cashName)
                                        @php
                                            $bank = $bankList->first();
                                        @endphp
                                        <div class="form-check mb-3">
                                            <input class="form-check-input cursor-pointer check-method-payment" type="radio" name="bank_id" id="bank-{{ $bank->id }}" value="{{ $bank->id }}" data-bank-name="{{ $name }}" data-show-upload="{{ $bank->upload }}" @if($bank->id == ($purchase->bank_id ?? 0)) checked @endif>
                                            <label class="form-check-label d-block cursor-pointer fw-bold" for="bank-{{ $bank->id }}">
                                                {{ $bank->account_name }}
                                            </label>
                                        </div>
                                    @else
                                        <div class="d-block mb-2 fw-bold text-decoration-underline">{{ $name }}</div>
                                        @foreach ($bankList as $bank)
                                            <div class="form-check mb-3">
                                                <input class="form-check-input cursor-pointer check-method-payment" type="radio" name="bank_id" id="bank-{{ $bank->id }}" value="{{ $bank->id }}" data-bank-name="{{ $name }}" data-account-name="{{ $bank->account_name }}" data-account-no="{{ $bank->account_no }}" data-show-upload="{{ $bank->upload }}" @if($bank->id == ($purchase->bank_id ?? 0)) checked @endif>
                                                <label class="form-check-label d-block cursor-pointer" for="bank-{{ $bank->id }}">
                                                    <span class="d-inline-block me-2 fw-bold">{{ $bank->account_no }}</span><span class="d-inline-block">a/n {{ $bank->account_name }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="col-12 d-md-none text-center" id="text-method-payment"></div>
                    <div class="col-md-6" id="col-bukti-transfer">
                        <label class="required">Bukti Transfer</label>
                        <label class="d-flex align-items-center justify-content-center p-1 border rounded bg-gray-300" style="cursor: pointer; min-height:150px;">
                            <input type="file" name="image" class="d-none" id="image" accept="image/jpeg,image/png" onchange="previewImage(this, '#preview-image')">
                            <img alt="Bukti Transfer" id="preview-image" src="{{ $purchase->image_url }}" style="height:auto; width:auto; max-width:100%;">
                        </label>
                    </div>
                </div>
            </div>
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

@push('styles')
<style>
    .table > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

@push('scripts')
<script>
    function previewImage(obj, target)
    {
        const [file] = obj.files;
        if (file) {
            $(target).attr('src', URL.createObjectURL(file));
        }
    }

    function applyCheckedMethodPayment(obj)
    {
        const uploader = $('#col-bukti-transfer').addClass('d-none');
        const target = $('#text-method-payment').empty();
        const radio = $(obj);
        const bankName = radio.data('bank-name');
        const accName = radio.data('account-name');
        const accNo = radio.data('account-no');
        const showUpload = (radio.data('show-upload') == 1);

        target.append($('<div></div>').addClass('fw-bold').html(bankName));

        if ((accName != undefined) && (accNo != undefined)) {
            target.append($('<div></div>').append('fw-bold').html(accNo));
            target.append($('<div></div>').html(accName));
        }

        if (showUpload) {
            uploader.removeClass('d-none');
        }
    }

    $(function() {
        $('.check-method-payment').on('change', function() {
            applyCheckedMethodPayment(this);
        });

        $('.check-method-payment:checked').change();
    });
</script>
@endpush