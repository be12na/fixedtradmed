@extends('layouts.app-member')

@php
    $indexRoute = route("member.payment.index");
@endphp

@section('content')
<form method="POST" action="{{ route('member.payment.saveTransfer', ['branchPaymentTransferable' => $branchPayment->id]) }}" id="myForm" enctype="multipart/form-data" data-alert-container="#alert-container">
    @csrf
    <div class="d-block" id="alert-container">
        @include('partials.alert')
    </div>
    <div class="fs-auto">
        <div class="row g-2 mb-2">
            <div class="col-sm-6 col-lg-4">
                <label>Tanggal</label>
                <input type="text" class="form-control" value="@formatFullDate(date('Y-m-d'), 'd F Y')" readonly>
            </div>
            <div class="col-sm-6 col-lg-4">
                <label>Cabang</label>
                <input type="text" class="form-control" value="{{ $branchPayment->branch->name }}" readonly>
            </div>
            <div class="col-lg-4">
                <label>Manager</label>
                <input type="text" class="form-control" value="{{ $branchPayment->manager->name }}" readonly>
            </div>
            <div class="col-12">
                <label>Keterangan</label>
                <textarea name="transfer_note" class="form-control" maxlength="250"></textarea>
            </div>
        </div>
        <div class="d-block mb-1 fw-bold">Detail Pembayaran</div>
        <div class="d-block p-1 border rounded-3 mb-3">
            <div class="d-block w-100 overflow-x-auto border">
                <table class="table table-sm table-nowrap table-detail mb-2">
                    <thead class="bg-gradient-brighten bg-white text-center">
                        <tr>
                            <th class="border-bottom">Produk</th>
                            <th class="border-bottom border-start">Jumlah</th>
                            <th class="border-bottom border-start">Harga</th>
                            <th class="border-bottom border-start">Jumlah</th>
                            <th class="border-bottom border-start">Diskon</th>
                            <th class="border-bottom border-start">Total</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @foreach ($branchPayment->details as $item)
                            <tr>
                                <td class="ps-4">{{ $item->product->name }}</td>
                                <td class="text-center">@formatNumber($item->product_qty, 0) {{ $item->product_unit_name }}</td>
                                <td class="text-end">@formatCurrency($item->product_price, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_price, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_discount, 0, true)</td>
                                <td class="text-end">@formatCurrency($item->total_price - $item->total_discount, 0, true)</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="fw-bold text-end">Total</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_price, 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_discount, 0, true)</td>
                            <td class="fw-bold text-end">@formatCurrency($branchPayment->total_transfer, 0, true)</td>
                        </tr>
                        <tr>
                            <td class="border-bottom-0" colspan="5">
                                <span class="d-inline-block fw-bold me-1">Total</span>
                                <span class="d-inline-block fst-italic small">( Yang harus ditransfer )</span>
                            </td>
                            <td class="text-end text-nowrap border-bottom-0 fw-bold" id="cell-total-transfer">@formatCurrency($branchPayment->total_transfer, 0, true)</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="d-block mb-1 fw-bold">Metode Pembayaran</div>
        <div class="d-block p-2 border rounded-3 mb-3">
            <div class="row g-2">
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
                        <img alt="Bukti Transfer" id="preview-image" src="" style="height:auto; width:auto; max-width:100%;">
                    </label>
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