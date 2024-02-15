@extends('layouts.app-main')

@php
    $hasAction = (($transfer->transfer_status == 0) && hasPermission('main.transfers.sales.action'));
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

                <div class="col-4 col-sm-3 {{ $labelCls }}">Tgl. Penjualan</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->transfer_date }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Cabang</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->branch->name }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Manager</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->manager->name }}</div>
                
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
                <div class="col-7 col-sm-8 {{ $valueCls }}">@formatFullDate($transfer->transfer_at ?? $transfer->created_at)</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Ket. Transfer</div>
                <div class="col-1 text-center">:</div>
                <div class="col-7 col-sm-8 {{ $valueCls }}">{{ $transfer->transfer_note ?? '-' }}</div>
                
                <div class="col-4 col-sm-3 {{ $labelCls }}">Status</div>
                <div class="col-1 text-center">:</div>
                @php
                    $color = 'bg-warning';
                    if ($transfer->transfer_status == PROCESS_STATUS_APPROVED) {
                        $color = 'bg-success text-light';
                    } elseif ($transfer->transfer_status == PROCESS_STATUS_REJECTED) {
                        $color = 'bg-danger text-light';
                    }
                @endphp
                <div class="col-7 col-sm-8 {{ $valueCls }}"><span class="py-1 px-2 {{ $color }}">{{ $transfer->status_text }}</span></div>
                
                @if ($transfer->transfer_status == PROCESS_STATUS_REJECTED)
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
            <div class="card fw-auto">
                <div class="card-header fw-bold">Detail Penjualan</div>
                <div class="card-body">
                    <div class="d-block w-100 overflow-x-auto">
                        <table class="table table-sm table-nowrap table-striped table-hover mb-1">
                            <thead class="bg-gradient-brighten bg-white">
                                <tr class="text-center align-middle">
                                    <th>Kode</th>
                                    <th class="border-start">Produk</th>
                                    <th class="border-start">Total</th>
                                </tr>
                            </thead>

                            @php
                                $totalSale = 0;
                            @endphp

                            <tbody>
                            @foreach ($transfer->transferDetails as $detail)
                                @php
                                    $sale = $detail->branchSale;
                                    $totalSale += $rowSale = $sale->sum_total_price;
                                @endphp
                                <tr>
                                    <td>{{ $sale->code }}</td>
                                    <td class="px-0">
                                        <table class="table table-sm table-nowrap w-100 mb-0">
                                            <tbody>
                                                @foreach ($sale->products->sortBy('product_name') as $product)
                                                <tr>
                                                    <td class="border-bottom-0">{{ $product->product_name }}</td>
                                                    <td class="border-bottom-0 text-end">@formatCurrency($product->product_price, 0, true, false)</td>
                                                    <td class="border-bottom-0 text-center">
                                                        <span>@formatNumber($product->product_qty, 0, true, false)</span>
                                                        <span>{{ $product->product_unit_name }}</span>
                                                    </td>
                                                    <td class="border-bottom-0 text-end">@formatCurrency($product->total_price, 0, true, false)</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                    <td class="text-end">@formatCurrency($rowSale, 0, true)</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td></td>
                                    <td class="text-end">Total</td>
                                    <td class="text-end fw-bold">@formatCurrency($totalSale, 0, true)</td>
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
        <form class="modal-content" method="POST" action="{{ route('main.transfers.sales.action', ['branchTransfer' => $transfer->id]) }}" id="myConfirmForm" data-alert-container="#alert-confirm-container">
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
        <form class="modal-content" method="POST" action="{{ route('main.transfers.sales.action', ['branchTransfer' => $transfer->id]) }}" id="myRejectForm" data-alert-container="#alert-reject-container">
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
@endsection
