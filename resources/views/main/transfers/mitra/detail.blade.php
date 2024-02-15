@extends('layouts.app-main')

@php
    $hasAction = (($transfer->purchase_status == PROCESS_STATUS_PENDING) && ($transfer->is_transfer) && hasPermission('main.transfers.mitra.action'));
@endphp

@section('content')
@include('partials.alert')

<div class="d-block fs-auto">
    <div class="row g-2 mb-3">
        @php
            $labelCls = ($transfer->bank_id > 0) ? 'col-md-4' : 'col-md-2';
            $valueCls = ($transfer->bank_id > 0) ? 'col-md-7' : 'col-md-9';
        @endphp
        <div class="@if ($transfer->bank_id > 0) col-md-6 @else col-12 @endif">
            <div class="row g-1 g-md-2">
                <div class="col-4 col-sm-3 {{ $labelCls }}">Kode</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->code }}</div>

                <div class="col-4 col-sm-3 {{ $labelCls }}">Tgl. Pembelian</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->purchase_date }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Member</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">
                    <div>{{ $transfer->mitra->name }}</div>
                    <div class="text-primary">
                        {{-- @if (!$transfer->is_v2)
                            <div>Cabang : {{ $transfer->mitra->branch->name }}</div>
                        @endif --}}
                        <div>Referral : {{ $transfer->mitra->referral ? $transfer->mitra->referral->name : env('APP_COMPANY') }}</div>
                    </div>
                </div>
                
                {{-- <div class="col-4 col-sm-3 {{ $labelCls }}">Cabang Pembelian</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">
                    <div>{{ $transfer->branch->name }}</div>
                    <div class="text-primary">Manager : {{ $transfer->manager->name ?? '-' }}</div>
                </div> --}}
                
                {{-- <div class="col-4 col-sm-3 {{ $labelCls }}">Manager</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->manager->name ?? '-' }}</div> --}}
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Bank</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">
                    @if ($transfer->bank_id == 0)
                        {{ $transfer->bank_code }}
                    @else
                        <div>{{ $transfer->bank_name }}</div>
                        <div>{{ $transfer->account_no }}</div>
                        <div>{{ $transfer->account_name }}</div>
                    @endif
                </div>

                <div class="col-4 col-sm-3 {{ $labelCls }}">Tgl. Transfer</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">@formatFullDate($transfer->getRawOriginal('transfer_at') ?? $transfer->created_at)</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Ket. Transfer</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->transfer_note ?? '-' }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Status</div>
                <div class="col-1 text-center">:</div>
                @php
                    $color = 'bg-warning';
                    if ($transfer->purchase_status == PROCESS_STATUS_APPROVED) {
                        $color = 'bg-success text-light';
                    } elseif ($transfer->purchase_status == PROCESS_STATUS_REJECTED) {
                        $color = 'bg-danger text-light';
                    }
                @endphp
                <div class="col-7 col-sm-8 {{ $valueCls }}"><span class="py-1 px-2 {{ $color }}">{{ $transfer->status_text }}</span></div>
                
                @if ($transfer->purchase_status == PROCESS_STATUS_REJECTED)
                    <div class="col-4 col-sm-3 {{ $labelCls }}">Ket. Status</div>
                    <div class="col-1 text-center">:</div>
                    <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->status_note ?? '-' }}</div>
                @endif
            </div>
        </div>
        @if ($transfer->bank_id > 0)
        <div class="col-md-6">
            <div class="d-block border rounded-3 p-2">
                <div class="fw-bold text-center">Bukti Transfer</div>
                <div class="d-flex align-items-center justify-content-center p-2 bg-gray-200">
                    <img alt="Bukti Transfer" src="{{ $transfer->image_url }}" style="height:auto; max-height: 300px; width:auto; max-width:100%;" class="cursor-pointer" data-bs-toggle="modal" data-bs-target="#modalZoomImage">
                </div>
            </div>
        </div>
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-header fw-bold">Detail Penjualan</div>
                <div class="card-body">
                    <div class="d-block w-100 overflow-x-auto">
                        <table class="table table-sm table-nowrap table-hover mb-1">
                            <thead class="bg-gradient-brighten bg-white text-center align-middle">
                                {{-- @if($transfer->is_v2)
                                    <tr>
                                        <th class="border-bottom" rowspan="2">Produk</th>
                                        <th class="border-bottom border-start" rowspan="2">Jumlah</th>
                                        <th class="border-bottom border-start" colspan="2">Harga</th>
                                        <th class="border-bottom border-start" rowspan="2">Diskon</th>
                                        <th class="border-bottom border-start" rowspan="2">Jumlah</th>
                                        <th class="border-bottom border-start" rowspan="2">Potongan</th>
                                        <th class="border-bottom border-start" rowspan="2">Total</th>
                                    </tr>
                                    <tr>
                                        <th class="border-bottom border-start">Normal</th>
                                        <th class="border-bottom border-start">Promo</th>
                                    </tr>
                                @else --}}
                                    <tr>
                                        <th>Produk</th>
                                        <th class="border-start">Harga</th>
                                        <th class="border-start">QTY</th>
                                        <th class="border-start">Total</th>
                                    </tr>
                                {{-- @endif --}}
                            </thead>
                            <tbody>
                                @foreach ($transfer->products as $detail)
                                    <tr>
                                        {{-- @if($transfer->is_v2)
                                            <td>{{ $detail->product->name }}</td>
                                            <td class="text-end">@formatNumber($detail->product_qty)</td>
                                            <td class="text-end">@formatCurrency($detail->product_price, 0, true)</td>
                                            <td class="text-end">@formatCurrency($detail->product_zone_price, 0, true)</td>
                                            <td class="text-end">@formatCurrency($detail->discount, 0, true)</td>
                                            @php
                                                $jumlah = ($detail->actual_price - $detail->discount) * $detail->product_qty;
                                                $total = $detail->total_price;
                                                $potongan = 0;
                                                $selisih = $jumlah - $total;
                                                if ($selisih > 0) {
                                                    $potongan = $selisih;
                                                }
                                            @endphp
                                            <td class="text-end">@formatCurrency(($detail->actual_price - $detail->discount) * $detail->product_qty, 0, true)</td>
                                            <td class="text-end">@formatCurrency($potongan, 0, true)</td>
                                            <td class="text-end">@formatCurrency($detail->total_price, 0, true)</td>
                                        @else --}}
                                            <td>{{ $detail->product->name }}</td>
                                            <td class="text-end">@formatCurrency($detail->product_price, 0, true)</td>
                                            <td class="text-end">@formatNumber($detail->product_qty)</td>
                                            <td class="text-end">@formatCurrency($detail->total_price, 0, true)</td>
                                        {{-- @endif --}}
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot style="border-top-width: 2px;">
                                <tr>
                                    <td class="fw-bold" colspan="{{ $transfer->is_v2 ? 7 : 3 }}">{{ $transfer->is_v2 ? 'Total' : 'Sub Total' }}</td>
                                    <td class="text-end fw-bold">@formatCurrency($transfer->total_purchase, 0, true)</td>
                                </tr>
                                @if(!$transfer->is_v2)
                                <tr>
                                    <td colspan="3">
                                        <span class="d-inline-block fw-bold me-1">Diskon</span>
                                        @if ($transfer->discount_percent > 0)
                                        <span class="d-inline-block small">
                                            (@formatAutoNumber($transfer->discount_percent, false, 2)%)
                                        </span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        @formatCurrency($transfer->discount_amount, 0, true)
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-wrap">
                                        <span class="d-inline-block fw-bold me-1">Total</span>
                                        <span class="d-inline-block fst-italic small">( Yang harus ditransfer )</span>
                                    </td>
                                    <td class="text-end fw-bold border-top border-top-dark">
                                        @formatCurrency($transfer->total_transfer, 0, true)
                                    </td>
                                </tr>
                                @endif
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
        <a class="btn btn-sm btn-warning" href="{{ route('main.transfers.mitra.index') }}">
            <i class="fa-solid fa-undo me-1"></i>
            Kembali
        </a>
    </div>
</div>

@endsection


@push('includeContent')
@if ($transfer->bank_id > 0)
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
                <img src="{{ $transfer->image_url }}">
            </div>
        </div>
    </div>
</div>
@endif

@if ($hasAction)
<div class="modal fade" id="myConfirm" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <form class="modal-content" method="POST" action="{{ route('main.transfers.mitra.action', ['mitraPurchase' => $transfer->id]) }}" id="myConfirmForm" data-alert-container="#alert-confirm-container">
            <div class="modal-body">
                @csrf
                <div class="d-block" id="alert-confirm-container"></div>
                <h4 class="text-center mb-0">
                    Konfirmasi data transfer?
                </h4>
                <input type="hidden" name="action_mode" value="{{ PROCESS_STATUS_APPROVED }}">
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
        <form class="modal-content" method="POST" action="{{ route('main.transfers.mitra.action', ['mitraPurchase' => $transfer->id]) }}" id="myRejectForm" data-alert-container="#alert-reject-container">
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
                <input type="hidden" name="action_mode" value="{{ PROCESS_STATUS_REJECTED }}">
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

@push('styles')
<style>
    .table > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

@if ($hasAction)
@push('scripts')
<script>
    $(function() {
        $('#myReject, #myConfirm').on('show.bs.modal', function() {
            const me = $(this);
            const frm = $('form', me);
            $(frm.data('alert-container')).empty();
        });
    });
</script>
@endpush
@endif
