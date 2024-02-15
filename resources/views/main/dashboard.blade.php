@extends('layouts.app-main')

@section('content')
    @include('partials.alert')
    {{-- <div class="d-flex flex-nowrap align-items-center mb-2">
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
                    <div class="fs-2 dashboard-value" data-dashboard="s=mitra&d=summary&t=month"></div>
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
                        <div class="fs-2 dashboard-value" data-dashboard="s=mitra&d=summary&t=today"></div>
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
                        <div class="fs-2 dashboard-value" data-dashboard="s=mitra&d=summary&t=shopper"></div>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- <div class="d-flex flex-nowrap align-items-center mb-2">
        <div class="flex-fill border-bottom"></div>
        <div class="mx-2">Partnership</div>
        <div class="flex-fill border-bottom"></div>
    </div>
    <div class="row g-2">
        @foreach ($products as $product)
            <div class="col-sm-4 d-flex">
                <div class="d-flex w-100 flex-nowrap text-nowrap hover-shadow shadow-sm border bg-gray-300 bg-gradient rounded-3 p-3"
                    style="--bs-bg-opacity:0.25; --bs-text-opacity:0.75;">
                    <div class="d-flex flex-column align-items-center text-center flex-grow-0" style="flex-basis:80px;">
                        <div class="flex-fill d-flex align-items-center justify-content-center text-primary fs-1 mb-2">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        <div class="fs-auto">{{ $product->code }}</div>
                    </div>
                    <div class="flex-fill d-flex align-items-center justify-content-end fs-1 dashboard-value"
                        data-dashboard="s=mitra&d=summary&t=mitra&p={{ $product->package_range }}"></div>
                </div>
            </div>
        @endforeach

        <div class="col-sm-4 d-flex">
            <div class="d-flex w-100 flex-nowrap text-nowrap hover-shadow shadow-sm border bg-gray-300 bg-gradient rounded-3 p-3"
                style="--bs-bg-opacity:0.25; --bs-text-opacity:0.75;">
                <div class="d-flex flex-column align-items-center text-center flex-grow-0" style="flex-basis:80px;">
                    <div class="flex-fill d-flex align-items-center justify-content-center text-primary fs-1 mb-2">
                        <i class="fa-solid fa-house-user"></i>
                    </div>
                    <div class="fs-auto">DROPSHIPPER</div>
                </div>
                <div class="flex-fill d-flex align-items-center justify-content-end fs-1 dashboard-value"
                    data-dashboard="s=mitra&d=summary&t=mitra&p=0"></div>
            </div>
        </div>
    </div> --}}
@endsection

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
@endpush
