@php
    $isTransferred = $userPackage->is_transferred;
    $isRejected = $userPackage->is_rejected;
    $isConfirmed = $userPackage->is_confirmed;
    $isRO = $userPackage->repeat_order;
    $transferMsg = 'Silahkan lakukan transfer sesuai nilai total transfer.';
    $transferCls = $isTransferred ? 'info' : 'warning';
@endphp

@extends('layouts.app-mitra')

@section('content')
    @include('partials.alert')
    @if ($isRO)
        @include('partials.alert', [
            'message' => $isTransferred ? 'Transaksi ada masih dalam proses konfirmasi.' : $transferMsg,
            'messageClass' => $transferCls,
            'showBtnClose' => false,
        ])
    @else
        @if (!$isConfirmed && !$isTranferred)
            @include('partials.alert', [
                'message' => $isTransferred ? 'Akun anda belum aktif sampai transaksi paket anda dikonfirmasi oleh Admin kami.' : $transferMsg,
                'messageClass' => $transferCls,
                'showBtnClose' => false,
            ])
        @endif
    @endif

    <div class="d-block fs-auto">
        <div class="row g-2">
            <div class="col-md-6 d-flex">
                <div class="p-3 flex-fill border rounded-3 bg-light">
                    <div class="row row-cols-2 g-2">
                        <div class="col d-flex justify-content-between">
                            <span>Invoice</span>
                            <span>:</span>
                        </div>
                        <div class="col fw-bold">{{ $userPackage->code }}</div>
                        <div class="col d-flex justify-content-between">
                            <span>Jenis</span>
                            <span>:</span>
                        </div>
                        <div class="col">{{ $userPackage->type_name }}</div>
                        <div class="col d-flex justify-content-between">
                            <span>Tanggal</span>
                            <span>:</span>
                        </div>
                        <div class="col">@formatDatetime($userPackage->created_at, __('format.date.full'))</div>
                        <div class="col d-flex justify-content-between">
                            <span>Paket</span>
                            <span>:</span>
                        </div>
                        <div class="col">{{ $userPackage->package_name }}</div>
                        @if ($userPackage->total_price > 0)
                            <div class="col d-flex justify-content-between">
                                <span>Harga</span>
                                <span>:</span>
                            </div>
                            <div class="col">@formatCurrency($userPackage->price)</div>
                            <div class="col d-flex justify-content-between">
                                <span>Kode Unik</span>
                                <span>:</span>
                            </div>
                            <div class="col">@formatCurrency($userPackage->digit)</div>
                            <div class="col d-flex justify-content-between">
                                <span>Total Transfer</span>
                                <span>:</span>
                            </div>
                            <div class="col">@formatCurrency($userPackage->total_price)</div>
                        @endif
                        <div class="col d-flex justify-content-between">
                            <span>Status</span>
                            <span>:</span>
                        </div>
                        <div class="col">{{ $isTransferred ? 'Menunggu Konfirmasi' : ($isRejected ? 'Ditolak' : ($isConfirmed ? 'Tuntas' : 'Transfer')) }}</div>
                        @if ($isRejected)
                            <div class="col d-flex justify-content-between">
                                <span>Keterangan</span>
                                <span>:</span>
                            </div>
                            <div class="col">{{ $userPackage->note }}</div>
                        @endif
                    </div>
                    @if ($isRO || (!$isRO && $isConfirmed))
                        <div class="mt-3">
                            <a href="{{ route('mitra.package.history') }}" class="btn btn-sm btn-primary">
                                <i class="fa fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            @if ($showBanks === true)
                <div class="col-md-6 d-flex">
                    <div class="p-3 flex-fill border rounded-3 bg-light">
                        @if ($isTransferred || $isConfirmed)
                            <div class="row row-cols-2 g-2">
                                <div class="col d-flex justify-content-between">
                                    <span>Bank</span>
                                    <span>:</span>
                                </div>
                                <div class="col">{{ $userPackage->bank_name }}</div>
                                <div class="col d-flex justify-content-between">
                                    <span>No. Rekening</span>
                                    <span>:</span>
                                </div>
                                <div class="col">{{ $userPackage->account_no }}</div>
                                <div class="col d-flex justify-content-between">
                                    <span>Pemilik Rekening</span>
                                    <span>:</span>
                                </div>
                                <div class="col">{{ $userPackage->account_name }}</div>
                            </div>
                        @else
                            <form method="POST" action="{{ $postUrl }}">
                                @csrf
                                <input type="hidden" name="trans_id" value="{{ $userPackage->id }}">
                                <div class="mb-2">
                                    <label class="d-block required fw-bold mb-3">Pilih Rekening</label>
                                    @foreach ($banks as $name => $bankList)
                                        <div class="d-block mb-2 fw-bold text-decoration-underline">{{ $name }}</div>
                                        @foreach ($bankList as $bank)
                                            <div class="form-check mb-3">
                                                <input class="form-check-input cursor-pointer" type="radio" name="bank_id" id="bank-{{ $bank->id }}" value="{{ $bank->id }}" autocomplete="off">
                                                <label class="form-check-label d-block cursor-pointer" for="bank-{{ $bank->id }}">
                                                    <span class="d-inline-block me-2 fw-bold">{{ $bank->account_no }}</span><span class="d-inline-block">a/n {{ $bank->account_name }}</span>
                                                </label>
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-block btn-primary">
                                    <i class="fa fa-paper-plane me-2"></i>
                                    Submit
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection