<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
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
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            padding: 0;
            height: 100vh;
            overflow: hidden;
            background-image: url("{{ asset('images/bg-tradmed.jpg') }}");
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .register-container {
            display: block;
            width: 100%;
            max-width: 384px;
        }
        .register-box {
            display: flex;
            flex-direction: column;
            background-color: rgba(var(--bs-white-rgb), 0.9);
            border: 1px solid rgba(var(--bs-white-rgb), 1);
            border-radius: 0.3rem;
        }
        .register-box .form-control {
            background-color: rgba(var(--bs-white-rgb), .5) !important;
        }
        .register-box .input-group-text {
            background-color: rgba(var(--bs-light-rgb), .5) !important;
        }
        @media (min-width: 768px) {
            .register-box {
                min-height: 100vh;
                border: 0;
                border-radius: 0;
            }
        }
        @media (min-width: 992px) {
            .register-container {
                max-width: 496px;
            }
        }
        #processing-overlay {
            display: none;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            overflow: hidden;
            background-color: rgba(var(--bs-white-rgb), 0.1);
            z-index: 9999999999;
        }
        #processing-overlay::before {
            content: "";
            display: block;
            width: 0;
            height: 4px;
            background: var(--bs-light) linear-gradient(90deg, rgba(var(--bs-danger-rgb), 0.5), rgba(var(--bs-success-rgb), 0.5), rgba(var(--bs-primary-rgb), 0.5));
        }
        #processing-overlay.show {
            display: flex;
        }
        #processing-overlay.show::before {
            animation: loading-bar 1s ease;
        }
        @keyframes loading-bar {
            0% {
                width: 0;
            }
            25% {
                width: 75%;
            }
            100% {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="vh-100 d-flex justify-content-center justify-content-md-end overflow-auto">
        <div class="register-container">
            <div class="register-box my-3 mx-2 m-md-0 py-2 px-3 py-md-3 px-md-5">
                <div class="d-flex justify-content-center mb-2">
                    <img alt="LOGO" src="{{ asset('images/logo-main.png') }}" style="max-height: 40px;">
                </div>
                <div class="h5 text-center text-decoration-underline mb-1">Daftar Affiliate</div>
                <div class="text-center mb-3">
                    <span class="me-2">Referral:</span>
                    <span class="fw-bold">{{ $isBasic ? $referral->name : env('APP_COMPANY') }}</span>
                </div>
                @include('partials.alert')
                <form method="POST" action="{{ $postUrl }}">
                    @csrf
                    <div class="d-block mb-2">
                        <label class="d-block">Paket</label>
                        <div class="form-control">{{ $isBasic ? 'Basic' : 'Premium' }}</div>
                    </div>
                    <div class="d-block mb-2">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="off" required autofocus>
                    </div>
                    <div class="d-block mb-2">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Password" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Ulangi Password</label>
                        <input type="password" class="form-control" name="password_confirmation" placeholder="Ulangi Password" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Nama Lengkap</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Nama Lengkap" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>No. Identitas</label>
                        <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="No. Identitas" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>No. Handphone</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}" placeholder="No. Handphone" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Nama Toko</label>
                        <input type="text" class="form-control" name="market_name" value="{{ old('market_name') }}" placeholder="Nama Toko" autocomplete="off" required>
                    </div>
                    <div class="mb-3"></div>
                    <div class="d-block text-center">
                        <button type="submit" class="btn btn-primary btn-block mb-3">Register</button>
                        <a class="text-decoration-none mt-3" href="{{ route('login') }}">
                            Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="processing-overlay" class="show"></div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        function showMainProcessing()
        {
            $('#processing-overlay').addClass('show');
        }
        function stopMainProcessing()
        {
            let processTime;
            processTime = setTimeout(function() {
                $('#processing-overlay').removeClass('show');
                $('#username').focus();
                clearTimeout(processTime);
            }, 500);
        }
        $(function() {
            stopMainProcessing();
        });
    </script>
</body>
</html>
