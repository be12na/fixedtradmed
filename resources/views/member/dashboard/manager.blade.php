@extends('layouts.app-member')

@section('content')
@include('partials.alert')
{{-- <div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Penjualan Team</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-days"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">{{ $monthTitle }}</div>
                <div class="fs-3 dashboard-value" data-dashboard="s=team&t=month"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-week"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Ini</div>
                <div class="fs-3 dashboard-value" data-dashboard="s=team&t=current"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-check"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Lalu</div>
                <div class="fs-3 dashboard-value" data-dashboard="s=team&t=last"></div>
            </div>
        </div>
    </div>
</div> --}}

<div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Penjualan</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-days"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">{{ $monthTitle }}</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=self&t=month"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-week"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Ini</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=self&t=current"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-check"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Lalu</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=self&t=last"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="flex-fill d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-gray-300 rounded-3 bg-gray-300 bg-gradient text-nowrap">
            <div class="fs-3"><i class="fa fa-coins"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Bonus Manager</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=self&t=total-month-bonus-sale"></div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Belanja Member</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-days"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">{{ $monthTitle }}</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=mitra&t=month"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-week"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Ini</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=mitra&t=current"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-check"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Lalu</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=mitra&t=last"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-gray-300 rounded-3 bg-gray-300 bg-gradient text-nowrap">
            <div class="fs-3"><i class="fa fa-coins"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Bonus Referral</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=bonus&t=total-bonus-referral"></div>
            </div>
        </div>
    </div>
</div>

@authIsManagerDistributor
<div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Bonus</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-sm-6 col-lg-3 d-flex">
        <div class="flex-fill d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-people-carry-box"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Bonus Distributor</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=bonus&t=total-bonus-dc"></div>
            </div>
        </div>
    </div>
</div>
@endauthIsManagerDistributor

<div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Shortcut</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-2">
    @canSale()
    <div class="col-auto d-flex">
        <a href="{{ route('member.sale.create') }}" class="btn btn-outline-primary" title="Tambah Pembelian">
            <i class="d-block fa-solid fa-file-circle-plus fs-5"></i>
            Penjualan
        </a>
    </div>
    @endcanSale
    @hasPermission('member.transfer.create')
    <div class="col-auto d-flex">
        <a href="{{ route('member.transfer.create') }}" class="btn btn-outline-primary" title="Tambah Transfer Penjualan">
            <i class="d-block fa-solid fa-money-bill-transfer fs-5"></i>
            Transfer
        </a>
    </div>
    @endhasPermission
</div>
{{-- @referralLinkHtml() --}}
@endsection

@push('scripts')
<script>
    $(function() {
        $('.dashboard-value').each(function(e) {
            const me = $(this);
            const url = '{{ route("dashboard") }}?' + me.data('dashboard');

            me.empty();

            $.get({
                url: url
            }).done(function(respon) {
                me.html(respon);
            }).fail(function(respon) {
                me.html('<span class="text-danger">0</span>');
            });
        });
    });
</script>
{{-- @referralLinkScript() --}}
@endpush
