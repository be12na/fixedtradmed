<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ config('app.name', 'Laravel') }}
        @if (isset($windowTitle))
            - {{ $windowTitle }}
        @endif
        @yield('windowTitle')
    </title>
    <link rel="icon" sizes="32x32" href="{{ asset('images/favicon-32.png') }}" type="image/png">
    <link rel="icon" sizes="16x16" href="{{ asset('images/favicon-16.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/x-icon">
    @if (config('app.env') == 'production')
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">

    @if (isset($vendorCSS))
        @php
            if (!is_array($vendorCSS)) {
                $vendorJS = [$vendorCSS];
            }
        @endphp
        @foreach ($vendorCSS as $vendor)
            <link rel="stylesheet" href="{{ asset($vendor) }}">
        @endforeach
    @endif

    @stack('vendorCSS')

    <link rel="stylesheet" href="{{ asset('css/app.css?' . $vFile) }}">
    <link rel="stylesheet" href="{{ asset('css/style.css?' . $vFile) }}">
    <link rel="stylesheet" href="{{ asset('css/custom-bs-border.css?' . $vFile) }}">
    <link rel="stylesheet" href="{{ asset('css/custom-bs-color.css?' . $vFile) }}">

    @if (isset($customStyle))
        <style>
            {!! $customStyle !!}
        </style>
    @endif

    <link rel="stylesheet" href="{{ asset('css/themes/member.css?' . $vFile) }}">

    @stack('styles')
</head>

<body class="@yield('bodyClass')">
    @include('layouts.sidebars.member')
    <div class="content">
        <div class="content-header shadow-sm">
            @include('layouts.navbars.member')
            <div
                class="container-fluid d-flex justify-content-center justify-content-md-start bg-light small px-3 py-2 border-bottom">
                <ul class="breadcrumb mb-0">
                    @if (isset($breadcrumbs) && is_array($breadcrumbs))
                        @for ($b = 0; $b < count($breadcrumbs); $b++)
                            <li class="breadcrumb-item @if ($b == count($breadcrumbs) - 1) active @endif">
                                {{ $breadcrumbs[$b] }}</li>
                        @endfor
                    @endif
                    @yield('breadcrumb')
                </ul>
            </div>
        </div>
        <div class="container-fluid gx-3 pt-3 pb-5 pb-md-3 flex-fill overflow-auto">
            @yield('content')
        </div>
        <footer
            class="d-flex align-items justify-content-between m-0 px-1 py-1 border-top bg-light text-gray-500 lh-1 small fs-auto">
            <span class="text-nowrap">{{ env('APP_COMPANY', 'Tradmed') }} &copy;
                {{ env('APP_COPYRIGHT', '2024') }}</span>
            <span>V.{{ env('APP_VERSION', '1') }}</span>
        </footer>
    </div>
    <div id="processing-overlay" class="show"></div>

    @stack('includeContent')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    @if (isset($vendorJS))
        @php
            if (!is_array($vendorJS)) {
                $vendorJS = [$vendorJS];
            }
        @endphp
        @foreach ($vendorJS as $vendor)
            <script src="{{ asset($vendor) }}"></script>
        @endforeach
    @endif

    @stack('vendorJS')

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/app.js?' . $vFile) }}"></script>

    @if (isset($customScript))
        <script>
            {!! $customScript !!}
        </script>
    @endif

    @stack('scripts')
</body>

</html>
