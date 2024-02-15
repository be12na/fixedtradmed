@extends('layouts.app-main')

@php
    $hasAction = (($branchPayment->transfer_status == PAYMENT_STATUS_TRANSFERRED) && hasPermission('main.payments.action'));
@endphp

@section('content')
@include('partials.alert')

<div class="d-block fs-auto">
    <div class="row g-2 mb-3">
        @php
            $labelCls = ($branchPayment->bank_id > 0) ? 'col-md-4' : 'col-md-2';
            $valueCls = ($branchPayment->bank_id > 0) ? 'col-md-7' : 'col-md-9';
        @endphp
        <div class="@if ($branchPayment->bank_id > 0) col-md-6 @else col-12 @endif">
            <div class="row g-1 g-md-2">
                <div class="col-4 col-sm-3 {{ $labelCls }}">Kode</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->code }}</div>

                <div class="col-4 col-sm-3 {{ $labelCls }}">Tgl. Penjualan</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->transfer_date }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Cabang</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->branch->name }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Manager</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->manager->name }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Bank</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">
                    @if ($branchPayment->bank_id == 0)
                        {{ $branchPayment->bank_code }}
                    @else
                        <div>{{ $branchPayment->bank_name }}</div>
                        <div>{{ $branchPayment->account_no }}</div>
                        <div>{{ $branchPayment->account_name }}</div>
                    @endif
                </div>

                <div class="col-4 col-sm-3 {{ $labelCls }}">Tgl. Transfer</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">@formatFullDate($branchPayment->transfer_at ?? $branchPayment->created_at)</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Ket. Transfer</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->transfer_note ?? '-' }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Status</div>
                <div class="col-1 text-center">:</div>
                @php
                    $color = 'bg-warning';
                    if ($branchPayment->transfer_status == PAYMENT_STATUS_APPROVED) {
                        $color = 'bg-success text-light';
                    } elseif ($branchPayment->transfer_status == PAYMENT_STATUS_REJECTED) {
                        $color = 'bg-danger text-light';
                    }
                @endphp
                <div class="col-7 col-sm-8 {{ $valueCls }}"><span class="py-1 px-2 {{ $color }}">{{ $branchPayment->transfer_status_name }}</span></div>
                
                @if ($branchPayment->transfer_status == PAYMENT_STATUS_REJECTED)
                    <div class="col-4 col-sm-3 {{ $labelCls }}">Ket. Status</div>
                    <div class="col-1 text-center">:</div>
                    <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $branchPayment->status_note ?? '-' }}</div>
                @endif
            </div>
        </div>
        @if ($branchPayment->bank_id > 0)
        <div class="col-md-6">
            <div class="d-block border rounded-3 p-2">
                <div class="fw-bold text-center">Bukti Transfer</div>
                <div class="d-flex align-items-center justify-content-center p-2 bg-gray-200">
                    <img alt="Bukti Transfer" src="{{ $branchPayment->image_url }}" style="height:auto; max-height: 300px; width:auto; max-width:100%;" class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#modalZoomImage">
                </div>
            </div>
        </div>
        @endif
        <div class="col-12">
            <div class="card fw-auto">
                <div class="card-header fw-bold">Detail Pembayaran</div>
                <div class="card-body">
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
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-block">
        @if ($hasAction)
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#myConfirm">
            <i class="fa-solid fa-handshake me-1"></i>
            Konfirmasi
        </button>
        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#myReject">
            <i class="fa-solid fa-times me-1"></i>
            Tolak
        </button>
        @endif
        <a class="btn btn-sm btn-warning" href="{{ route('main.transfers.sales.index') }}">
            <i class="fa-solid fa-undo me-1"></i>
            Kembali
        </a>
    </div>
</div>

@push('includeContent')
@if ($branchPayment->bank_id > 0)
<div class="modal fade" id="modalZoomImage" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 px-3">
                <span class="fw-bold small">Bukti Transfer</span>
                <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body text-center image-zoom">
                <img src="{{ $branchPayment->image_url }}">
            </div>
        </div>
    </div>
</div>
@endif

@if ($hasAction)
<div class="modal fade" id="myConfirm" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" method="POST" action="{{ route('main.payments.action', ['branchPaymentApproving' => $branchPayment->id]) }}" id="myConfirmForm" data-alert-container="#alert-confirm-container">
            <div class="modal-body">
                @csrf
                <div class="d-block" id="alert-confirm-container"></div>
                <h4 class="text-center mb-0">
                    Konfirmasi data transfer pembayaran?
                </h4>
                <input type="hidden" name="action_mode" value="{{ PAYMENT_STATUS_APPROVED }}">
            </div>
            <div class="modal-footer py-1 px-3 justify-content-between">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="fa-solid fa-handshake me-1"></i>
                    Konfirmasi
                </button>
                <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
                    <i class="fa-solid fa-undo me-1"></i>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="myReject" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" action="{{ route('main.payments.action', ['branchPaymentApproving' => $branchPayment->id]) }}" id="myRejectForm" data-alert-container="#alert-reject-container">
            <div class="modal-header py-2 px-3">
                <span class="fw-bold small">Konfirmas Proses Penolakan</span>
                <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                @csrf
                <div class="d-block" id="alert-reject-container"></div>
                <div class="d-block">
                    <label class="required">Keterangan</label>
                    <textarea class="form-control" maxlength="250" name="status_note"></textarea>
                </div>
                <input type="hidden" name="action_mode" value="{{ PAYMENT_STATUS_REJECTED }}">
            </div>
            <div class="modal-footer py-1 px-3 justify-content-between">
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa-solid fa-times me-1"></i>
                    Tolak
                </button>
                <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
                    <i class="fa-solid fa-undo me-1"></i>
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@endpush
@endsection
