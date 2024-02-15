@extends('layouts.app-mitra')

@php
    $user = auth()->user();
    $mitraIcon = 'user-nurse';
    $mitraIconColor = 'text-gray-600';
    $mitraBadgeCls = 'bg-gray-500 text-white';

    if ($user->is_reseller) {
        $mitraIcon = 'user-tie';
        $mitraIconColor = 'text-success';
        $mitraBadgeCls = 'bg-success text-white';
    }
@endphp

@section('content')
    @include('partials.alert')
    <div class="row g-2 mb-3">
        <div class="col-md-4 d-flex">
            <div class="flex-fill d-flex flex-column flex-sm-row border rounded-3 shadow-sm p-2 p-md-3">
                <div
                    class="d-flex align-items-center justify-content-center mb-3 mb-sm-0 flex-shrink-0 {{ $mitraIconColor }}">
                    <i class="fa-solid fa-{{ $mitraIcon }}" style="font-size: 3.5rem;"></i>
                </div>
                <div class="flex-fill text-center">
                    <div class="text-decoration-underline fs-5 mb-2">{{ $user->name }}</div>
                    <div class="mb-2"><span
                            class="badge rounded-pill {{ $mitraBadgeCls }}">{{ $user->mitra_type_name }}</span></div>
                    
                    <div class="mt-2">
                        <a href="{{ route('mitra.purchase.create') }}"
                            class="btn btn-sm btn-link btn-link-primary shadow-none">
                            <i
                                class="fa fa-{{ $user->is_reseller ? 'rotate' : 'cart-plus' }} me-2"></i>{{ $user->is_reseller ? 'Repeat Order' : 'Belanja' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 d-flex">
            <div class="flex-fill d-block p-3 border rounded-3 shadow-sm">
                <div class="text-center text-sm-start mb-2">Toko</div>
                @include('partials.mitra-link', [
                    'user' => $user,
                    'contentMode' => 'html',
                    'linkUrl' => $user->mitra_store_url,
                ])
            </div>
        </div>
        <div class="col-md-4 d-flex">
            <div class="flex-fill d-block p-3 border rounded-3 shadow-sm">
                <div class="text-center text-sm-start mb-2">Referral</div>
                @include('partials.mitra-link', [
                    'user' => $user,
                    'contentMode' => 'html',
                    'linkUrl' => $user->mitra_referral_link_url,
                ])
            </div>
        </div>
    </div>

    <div class="d-flex flex-nowrap align-items-center mb-2">
        <div class="flex-fill border-bottom"></div>
        <div class="mx-2">Member</div>
        <div class="flex-fill border-bottom"></div>
    </div>
    <div class="row g-2 mb-3">
        <div class="col-lg-4 d-flex">
            <div
                class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
                <div class="fs-1"><i class="fa fa-calendar-days"></i></div>
                <div class="flex-fill text-center text-sm-end">
                    <div class="fs-auto text-decoration-underline">Member {{ $monthTitle }}</div>
                    <div class="fs-2 dashboard-value" data-dashboard="s=member&t=month"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div
                class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
                <div class="fs-1"><i class="fa fa-calendar-week"></i></div>
                <div class="flex-fill text-center text-md-end">
                    <div class="fs-auto text-decoration-underline">Member Hari Ini</div>
                    <div class="d-flex justify-content-center justify-content-md-end">
                        <div class="fs-2 dashboard-value" data-dashboard="s=member&t=today"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div
                class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
                <div class="fs-1"><i class="fa fa-calendar-check"></i></div>
                <div class="flex-fill text-center text-md-end">
                    <div class="fs-auto text-decoration-underline">Total Member</div>
                    <div class="d-flex justify-content-center justify-content-md-end">
                        <div class="fs-2 dashboard-value" data-dashboard="s=member&t=shopper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- @if ($user->is_reseller)
@endif --}}

    {{-- <div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Pembelian</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-lg-4 d-flex">
        <div class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-days"></i></div>
            <div class="flex-fill text-center text-sm-end">
                <div class="fs-auto text-decoration-underline">{{ $monthTitle }}</div>
                <div class="fs-2 dashboard-value" data-dashboard="t=month"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-week"></i></div>
            <div class="flex-fill text-center text-md-end">
                <div class="fs-auto text-decoration-underline">Minggu Ini</div>
                <div class="d-flex justify-content-center justify-content-md-end">
                    <div class="fs-2 dashboard-value" data-dashboard="t=current"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="d-flex d-flex align-items-center justify-content-between flex-column flex-sm-row w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-1"><i class="fa fa-calendar-check"></i></div>
            <div class="flex-fill text-center text-md-end">
                <div class="fs-auto text-decoration-underline">Minggu Lalu</div>
                <div class="d-flex justify-content-center justify-content-md-end">
                    <div class="fs-2 dashboard-value" data-dashboard="t=last"></div>
                </div>
            </div>
        </div>
    </div>
</div> --}}

    {{-- <div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Belanja Anggota</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-days"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">{{ $monthTitle }}</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=direct&t=month"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-week"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Ini</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=direct&t=current"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-calendar-check"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Minggu Lalu</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=direct&t=last"></div>
            </div>
        </div>
    </div>
</div> --}}

    {{-- <div class="d-flex flex-nowrap align-items-center mb-2">
    <div class="flex-fill border-bottom"></div>
    <div class="mx-2">Bonus</div>
    <div class="flex-fill border-bottom"></div>
</div>
<div class="row g-2 mb-3">
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-coins"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Total Bonus</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=bonus&t=total"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-info rounded-3 bg-info bg-gradient text-nowrap">
            <div class="fs-3"><i class="fa fa-business-time"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Bonus Pribadi</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=bonus&t=self"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex">
        <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
            <div class="fs-3"><i class="fa fa-network-wired"></i></div>
            <div class="text-end">
                <div class="fs-auto text-decoration-underline">Bonus Upline</div>
                <div class="fs-4 dashboard-value" data-dashboard="s=bonus&t=upline"></div>
            </div>
        </div>
    </div>
</div> --}}
@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('scripts')
    <script>
        $(function() {
            $('.dashboard-value').each(function(e) {
                const me = $(this);
                const url = '{{ route('dashboard') }}?' + me.data('dashboard');

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
    @include('partials.mitra-link', ['contentMode' => 'script'])
@endpush
