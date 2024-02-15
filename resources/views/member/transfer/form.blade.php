@extends('layouts.app-member')

@php
    $indexRoute = route("member.transfer.index");
    $todayIndex = $dateRange[1]->dayOfWeek;

    $defaultDateValue = formatFullDate((clone $dateRange[1])->subDays(($todayIndex == 1) ? 2 : 1));
    $startDate = formatFullDate($dateRange[0]);
    $endDate = formatFullDate($dateRange[1]);
@endphp

@section('content')
<form method="POST" action="{{ route('member.transfer.store') }}" id="myForm" enctype="multipart/form-data" data-alert-container="#alert-container">
    @csrf
    <div class="d-block" id="alert-container">
        @include('partials.alert')
    </div>
    <div class="fs-auto">
        <div class="row g-2 mb-2">
            <div class="col-sm-6 col-lg-4">
                <label class="required">Tanggal</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white" name="transfer_date" id="transfer-date" autocomplete="off" value="{{ $defaultDateValue }}" data-date-format="d MM yyyy" data-date-start-date="{{ $startDate }}" data-date-end-date="{{ $endDate }}" readonly="">
                    <label for="transfer-date" class="input-group-text cursor-pointer">
                        <i class="fa-solid fa-calendar-days"></i>
                    </label>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <label class="required">Cabang</label>
                <select class="form-select" name="branch_id" id="branch-id" autocomplete="off">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4">
                <label>Manager</label>
                <input type="text" class="form-control" id="manage-name" readonly="">
            </div>
        </div>
        <div class="row g-2 mb-2">
            {{-- <div class="col-auto col-sm-6 col-md-4">
                <label class="d-block">Pemakaian Omzet</label>
                <input name="omzet_used" id="omzet-used" type="number" step="1" min="0" class="form-control" value="0" autocomplete="off">
            </div> --}}
            <div class="col-12">
                <label>Keterangan</label>
                <textarea name="transfer_note" class="form-control" maxlength="250"></textarea>
            </div>
        </div>
        <div class="d-block mb-1 fw-bold">Summary Penjualan</div>
        <div class="d-block p-1 border rounded-3 mb-3">
            <table class="table table-hover border-0 mb-0">
                <tbody>
                    <tr class="border-bottom">
                        <td class="fw-bold">Tanggal</td>
                        <td class="text-end text-nowrap" id="cell-tanggal"></td>
                    </tr>
                    <tr class="border-bottom">
                        <td class="fw-bold">Total Penjualan</td>
                        <td class="text-end text-nowrap" id="cell-total-sale">Rp. 0</td>
                    </tr>
                    {{-- <tr class="border-bottom">
                        <td class="fw-bold">Savings</td>
                        <td class="text-end text-nowrap" id="cell-total-saving">Rp. 0</td>
                    </tr>
                    <tr class="border-bottom">
                        <td class="fw-bold">Potongan</td>
                        <td class="text-end text-nowrap" id="cell-potongan">Rp. 0</td>
                    </tr> --}}
                </tbody>
                <tfoot>
                    <tr>
                        <td class="border-bottom-0">
                            <span class="d-inline-block fw-bold me-1">Total</span>
                            <span class="d-inline-block fst-italic small">( Yang harus ditransfer )</span>
                        </td>
                        <td class="text-end text-nowrap border-bottom-0 fw-bold" id="cell-total-transfer">Rp. 0</td>
                    </tr>
                </tfoot>
            </table>
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
    const omzetUsed = 0; //$('#omzet-used').val();
    cellTotalTransfer.html(formatCurrency( inputTotalSell - inputTotalSaving - inputPotongan - omzetUsed));
--}}

@push('scripts')
<script>
    let inputTotalSell = 0;
    let cellTotalSell, cellTotalTransfer;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function resetSummary()
    {
        inputTotalSell = 0;
        $('#manage-name').val('');
    }

    function calculateSummary()
    {
        cellTotalSell.html(formatCurrency(inputTotalSell));
        cellTotalTransfer.html(formatCurrency( inputTotalSell ));
    }

    function loadSummary()
    {
        const tgl = $('#transfer-date').val();
        const branchId = $('#branch-id').val();
        $('#cell-tanggal').html(tgl);

        resetSummary();
        calculateSummary();

        const data = {
            tanggal: tgl,
            branch_id: branchId
        };

        $.get({
            url: "{{ route('member.transfer.inputSummary') }}",
            data: data
        }).done(function(respon) {
            inputTotalSell = respon.total_sell;
            $('#manage-name').val(respon.manager);

            calculateSummary();
        }).fail(function(respon) {
            alert('Telah terjadi kesalahan pada server. silahkan coba lagi...');
        });
    }

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
        cellTotalSell = $("#cell-total-sale");
        // -------
        cellTotalSaving = $("#cell-total-saving");
        cellPotongan = $("#cell-potongan");
        cellTotalTransfer = $('#cell-total-transfer');

        $('#transfer-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true,
            daysOfWeekDisabled: [0]
        });

        $('#transfer-date, #branch-id').on('change', function() {
            loadSummary();
        });

        $('#omzet-used').on('change', function() {
            calculateSummary();
        });

        $('#branch-id').change();

        $('.check-method-payment').on('change', function() {
            applyCheckedMethodPayment(this);
        });

        $('.check-method-payment:checked').change();
    });
</script>
@endpush