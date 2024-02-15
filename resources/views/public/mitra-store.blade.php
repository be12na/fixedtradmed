<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | {{ $mitra->name }}</title>
    <link rel="icon" sizes="32x32" href="{{ asset('images/favicon-32.png') }}" type="image/png">
    <link rel="icon" sizes="16x16" href="{{ asset('images/favicon-16.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/x-icon">
    @if (isLive())
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css?t=' . mt_rand(1000001, 9999999)) }}">
    <style>
        body {
            --bs-my-pink-rgb: 255, 192, 203;
            background: var(--bs-white) linear-gradient(90deg, rgba(var(--bs-my-pink-rgb), 0.25) 45%, rgba(var(--bs-info-rgb), 0.25)) no-repeat;
        }
    </style>
</head>
@php
    $waNomor = isLive() ? $mitra->international_phone : env('APP_WA_CONTACT', '081200000000');
@endphp

<body>
    <div class="d-flex flex-column justify-content-between" style="min-height:100vh;">
        <div class="container py-4">
            <div class="bg-white p-2 p-md-3 mb-2 mb-md-3 rounded-3 fs-auto bg-opacity-50">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <div class="row g-2 g-md-3">
                            <div class="col-12 col-sm-auto d-flex align-items-center">
                                <img src="{{ asset('images/logo-main.png') }}" alt="{{ config('app.name') }}"
                                    class="flex-shrink-0" style="width:100%; max-width:100px; height:auto;">
                            </div>
                            <div class="col-12 col-sm-auto flex-sm-fill d-flex flex-column justify-conten-end">
                                <div>
                                    <span class="fs-2 fw-bold me-3">{{ $mitra->name }}</span>
                                    <sup class="fs-auto py-1 px-2 bg-primary text-light rounded-circle">
                                        <i class="fa fa-check"></i>
                                    </sup>
                                </div>
                                @php
                                    $infoMitra = ['Reseller ', config('app.name'), ' '];
                                    if ($mitra->city) {
                                        $infoMitra[] = $mitra->city;
                                    }
                                    if ($mitra->province) {
                                        if ($mitra->city) {
                                            $infoMitra[] = ', ';
                                        }
                                        $infoMitra[] = $mitra->province;
                                    }
                                    $textInfo = implode('', $infoMitra);
                                @endphp
                                <div class="fs-auto fw-bold">
                                    {{ $textInfo }}
                                </div>
                                <div class="small fst-italic">
                                    <span class="pb-1 border-bottom border-dark">Member ini telah terverifikasi</span>
                                </div>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-sm btn-warning btn-href"
                                        data-href="{{ $mitra->mitra_referral_link_url }}">Daftar Mitra</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5">
                @foreach ($products as $product)
                    @php
                        $harga = formatCurrency($product->harga_a, 0, true, false);
                        $waText = 'Halo Kak!!! Saya Mau ';
                        $waTextBeli = $waText . "Beli Produk {$product->category_name} - {$product->name}";
                        $waTextDetail = $waText . "Tanya Produk {$product->category_name} - {$product->name}";
                    @endphp
                    <div class="col d-flex">
                        <div
                            class="flex-fill d-flex flex-column justify-content-end p-1 p-md-2 border rounded-3 bg-white text-center fs-auto shadow bg-opacity-25">
                            <div class="mb-2">
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;">
                            </div>
                            <div class="border-bottom fw-bold pb-1 mb-1">{{ $product->name }}</div>
                            <div>Harga</div>
                            <div class="small mb-2">{!! $harga !!}</div>
                            <button type="button" class="btn btn-block btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#modal-menu">Beli</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <footer class="text-center py-1 text-secondary fs-auto" style="--bs-text-opacity:0.65;">Copyright 2024
            {{ config('app.name') }} | All Right Reserved</footer>
    </div>

    <div class="modal fade" id="modal-menu" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row g-2">
                        @foreach ($mitra->social_media_list as $sosmed)
                            <div class="col-12">
                                <button type="button"
                                    class="btn btn-block btn-sosmed @if ($sosmed['button']) btn-outline-{{ $sosmed['button'] }} @else btn-outline-secondary @endif"
                                    href="javascript:void();" data-sosmed-url="{{ $sosmed['url'] }}"
                                    title="{{ $sosmed['text'] }}">
                                    <i class="{{ $sosmed['icon'] }} me-2"></i>
                                    {{ $sosmed['text'] }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/app.js?' . $vFile) }}"></script>
    <script>
        $(function() {
            $('.btn-sosmed').on('click', function(e) {
                const me = $(this);
                const uri = me.data('sosmed-url');
                window.open(encodeURI(uri));

                return false;
            });
        });
    </script>
</body>

</html>
