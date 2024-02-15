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
    <link rel="icon" sizes="32x32" href="{{ asset('images/favicon-32.png?t=' . mt_rand(1000001, 9999999)) }}" type="image/png">
    <link rel="icon" sizes="16x16" href="{{ asset('images/favicon-16.png?t=' . mt_rand(1000001, 9999999)) }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png?t=' . mt_rand(1000001, 9999999)) }}" type="image/x-icon">
    @if (isLive())
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    @endif
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    
    @if (isset($vendorCSS))
        @php
            if (!is_array($vendorCSS)) $vendorJS = [$vendorCSS];
        @endphp
        @foreach ($vendorCSS as $vendor)
        <link rel="stylesheet" href="{{ asset($vendor) }}">
        @endforeach
    @endif
    
    @stack('vendorCSS')

    {{-- <link rel="stylesheet" href="{{ asset('css/app.css?t=' . mt_rand(1000001, 9999999)) }}"> --}}
    <link rel="stylesheet" href="{{ asset('css/style.css?t=' . mt_rand(1000001, 9999999)) }}">
    <link rel="stylesheet" href="{{ asset('css/custom-bs-border.css?t=' . mt_rand(1000001, 9999999)) }}">
    <link rel="stylesheet" href="{{ asset('css/custom-bs-color.css?t=' . mt_rand(1000001, 9999999)) }}">
    
    @if (isset($customStyle))
    <style>
        {!! $customStyle !!}
        </style>
    @endif
    
    @stack('styles')
</head>
<body class="@yield('bodyClass')">
    
    @yield('content')
    
    @stack('includeContent')

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    
    @if (isset($vendorJS))
        @php
            if (!is_array($vendorJS)) $vendorJS = [$vendorJS];
        @endphp
        @foreach ($vendorJS as $vendor)
        <script src="{{ asset($vendor) }}"></script>
        @endforeach
    @endif

    @stack('vendorJS')

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    
    @if (isset($customScript))
    <script>
        {!! $customScript !!}
    </script>
    @endif

    @stack('scripts')
</body>
</html>
