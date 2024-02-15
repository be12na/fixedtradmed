@php
    $user = isset($user) ? $user : auth()->user();
@endphp

@extends('layouts.app-mitra')

@section('content')
@include('partials.alert')
<div class="row g-2 mb-2">
    <div class="col-6 d-flex">
        <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
            <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total Poin</div>
            <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1" id="total-poin">@formatNumber($user->total_point, 0)</div>
        </div>
    </div>
    <div class="col-6 d-flex">
        <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
            <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Sisa Poin</div>
            <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1" id="sisa-poin">@formatNumber($user->total_remaining_point, 0)</div>
        </div>
    </div>
</div>
<div class="d-block w-100 table-responsive border">
    <table class="table table-sm table-nowrap table-hover fs-auto">
        <thead class="bg-gradient-brighten bg-white">
            <tr class="text-center">
                <th>No</th>
                <th class="border-start">Poin</th>
                <th class="border-start">Reward</th>
                <th class="border-start"></th>
            </tr>
        </thead>
        <tbody>
            @if ($rewards->isNotEmpty())
                @php
                    $no = 1;
                    $btnClaim = '<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-claim" data-modal-url="%s">Klaim</button>';
                @endphp
                @foreach ($rewards as $row)
                    @php
                        // $max = $row->max_point;
                        $rowBg = '';
                        $rowStyle = '';
                        
                        if (!$user->canClaimReward($row)) {
                            $claim = $user->dataClaimReward($row);
                            if (empty($claim)) {
                                $status = '-';
                            } else {
                                $rowStyle = '--bs-bg-opacity:0.125;';
                                $rowBg = $claim->is_finish ? 'bg-info' : 'bg-success';
                                $status = $claim->is_finish ? 'Tuntas' : 'Sedang diproses';
                            }
                        } else {
                            $routeClaim = route('mitra.point.reward.claim.show', ['point' => $row->point]);
                            $status = sprintf($btnClaim, $routeClaim);
                        }
                    @endphp
                    <tr class="{{ $rowBg }}" style="{{ $rowStyle }}">
                        <td class="text-center">{{ $no++ }}.</td>
                        <td>
                            <div class="d-flex flex-nowrap justify-content-center">
                                <span>@formatNumber($row->point, 0)</span>
                                {{-- @empty($max)
                                    <span class="ms-2">UP</span>
                                @else
                                    <span class="mx-2">-</span><span>@formatNumber($max, 0)</span>
                                @endempty --}}
                            </div>
                        </td>
                        <td>{{ $row->reward }}</td>
                        <td class="text-center">{!! $status !!}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" class="text-center">{{ __('datatable.zeroRecords') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'modal-claim', 'scrollable' => true])
@endpush

@push('scripts')
<script>
    $(function() {});
</script>
@endpush