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
            background-color: rgba(var(--bs-dark-rgb), 0.85);
            border: 1px solid rgba(var(--bs-white-rgb), 1);
            color: var(--bs-light);
            border-radius: 0.3rem;
        }

        /* .register-box .form-control {
            background-color: rgba(var(--bs-white-rgb), .5) !important;
        } */
        /* .register-box .input-group-text {
            background-color: rgba(var(--bs-light-rgb), .5) !important;
        } */
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
    <div class="vh-100 d-flex justify-content-center overflow-auto">
        <div class="register-container">
            <div class="register-box my-3 mx-2 m-md-0 py-2 px-3 py-md-3 px-md-5">
                <div class="d-flex justify-content-center mb-2">
                    <img alt="LOGO" src="{{ asset('images/logo-main.png') }}" style="max-height: 40px;">
                </div>
                <div class="mb-3">
                    <div class="text-center text-decoration-underline">Daftar Affiliate</div>
                    @if (!empty($referral))
                        <div class="text-center mt-1">
                            <span class="me-2">Referral:</span>
                            <span class="fw-bold">{{ $referral->name }}</span>
                        </div>
                    @endif
                </div>
                @include('partials.alert')
                <form method="POST" action="{{ $postUrl }}" class="disable-submit">
                    @csrf
                    @if (empty($referral))
                        <div class="d-block mb-2">
                            <label>Referral</label>
                            <input type="text" class="form-control" name="referral_username"
                                value="{{ old('referral_username') }}" placeholder="Referral" autocomplete="off"
                                required autofocus>
                        </div>
                    @else
                        <input type="hidden" name="referral_id" value="{{ $referral->id }}">
                    @endif
                    {{-- <div class="d-block mb-2">
                        <label class="d-block">Paket</label>
                        <select name="mitra_type" class="form-select" autocomplete="off" required>
                            <option value="">-- Pilih Paket --</option>
                            @foreach (MITRA_TYPES as $typeId => $typeName)
                                <option value="{{ $typeId }}" @optionSelected($typeId, old('mitra_type', -1))>{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <div class="d-block mb-2">
                        <label>Username</label>
                        <input type="text" class="form-control" name="username" value="{{ old('username') }}"
                            placeholder="Tanpa Spasi dan Tanpa Simbol" autocomplete="off" required
                            @if (!empty($referral)) autofocus @endif>
                    </div>
                    <div class="d-block mb-2">
                        <label>Password</label>
                        <div class="input-group">
                            <input type="password" id="password" class="form-control" name="password"
                                placeholder="Kombinasi Huruf dan Angka" data-icon-open="fa fa-eye"
                                data-icon-close="fa fa-eye-slash" autocomplete="off" required>
                            <label for="password"
                                class="input-group-text cursor-pointer pwd-view justify-content-center"
                                data-target="#password" style="width:44px;">
                                <i class="pwd-icon"></i>
                            </label>
                        </div>
                    </div>
                    <div class="d-block mb-2">
                        <label>Ulangi Password</label>
                        <div class="input-group">
                            <input type="password" id="password-confirm" class="form-control"
                                name="password_confirmation" placeholder="Ulangi Password" data-icon-open="fa fa-eye"
                                data-icon-close="fa fa-eye-slash" autocomplete="off" required>
                            <label for="password-confirm"
                                class="input-group-text cursor-pointer pwd-view justify-content-center"
                                data-target="#password-confirm" style="width:44px;">
                                <i class="pwd-icon"></i>
                            </label>
                        </div>
                    </div>
                    <div class="d-block mb-2">
                        <label>Nama</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}"
                            placeholder="Nama" autocomplete="off" required>
                    </div>
                    {{-- <div class="d-block mb-2">
                        <label>No. Identitas</label>
                        <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="No. Identitas" autocomplete="off" required>
                    </div> --}}
                    <div class="d-block mb-2">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"
                            placeholder="Email" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Whatsapp</label>
                        <input type="text" class="form-control" name="phone" value="{{ old('phone') }}"
                            placeholder="Whatsapp" autocomplete="off" required>
                    </div>
                    <div class="d-block mb-2">
                        <label>Nama Toko</label>
                        <input type="text" class="form-control" name="market_name"
                            value="{{ old('market_name') }}" placeholder="Tanpa Spasi dan Tanpa Simbol"
                            autocomplete="off" required>
                    </div>
                    <div class="mb-3"></div>
                    <div class="d-block text-center">
                        <button type="submit" class="btn btn-primary btn-block mb-3">Register</button>
                        <a class="text-decoration-none mt-3 link-info" href="{{ route('login') }}">
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
    <script src="{{ asset('js/app.js?' . $vFile) }}"></script>
</body>

</html>
