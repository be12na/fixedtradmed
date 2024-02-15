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

    {{-- @isLive
    {!! htmlScriptTagJsApi(['action' => 'login']) !!}
    @endisLive --}}

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body {
            padding: 0;
            overflow: hidden;
        }

        .login-page {
            background-image: url("{{ asset('images/bg-tradmed.jpg') }}");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        .login-box {
            --bs-login-bg: rgba(var(--bs-dark-rgb), .85);
            display: flex;
            flex-direction: column;
            width: 400px;
            max-width: 100%;
            margin-top: -110%;
            transition: margin-top 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .login-box.ready {
            margin-top: 0;
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1 1 auto;
            background-color: var(--bs-login-bg);
            border-radius: 30px 30px 0 0;
        }

        .login-logo>img {
            max-width: 80%;
        }

        .login-form {
            width: 100%;
            flex: 0 0 auto;
            border-radius: 0 0 30px 30px;
            background-color: var(--bs-login-bg);
        }

        /* .login-form .form-control {
            background-color: rgba(var(--bs-white-rgb), .5) !important;
        } */
        @media (min-width: 576px) {
            .login-box {
                flex-direction: row;
                width: 600px;
            }

            .login-logo {
                border-radius: 30px 0 0 30px;
            }

            .login-form {
                flex: 0 0 60%;
                border-radius: 0 30px 30px 0;
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
            background-color: rgba(var(--bs-white-rgb), 0.3);
            z-index: 9999999999;
        }

        #processing-overlay::before {
            content: "";
            display: block;
            width: 0;
            height: 4px;
            background: var(--bs-primary) linear-gradient(90deg, rgba(var(--bs-danger-rgb), 0.5), rgba(var(--bs-success-rgb), 0.5), rgba(var(--bs-primary-rgb), 0.5));
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
    <div class="d-block px-2 py-3 px-sm-3 vh-100 overflow-auto login-page">
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100%;">
            <div class="login-box">
                <div class="login-logo py-4 px-2">
                    <img alt="LOGO" src="{{ asset('images/logo-main.png') }}">
                </div>
                <div class="login-form d-flex flex-column justify-content-center h-100 py-3 px-4">
                    <h3 class="mb-3 text-center text-warning"
                        style="--bs-text-opacity:0.95; text-shadow: 1px 2px 2px var(--bs-white), 1px 2px 4px var(--bs-warning);">
                        Sign In</h3>

                    @error('username')
                        @include('partials.alert', [
                            'message' => 'Username dan password salah.',
                            'messageClass' => 'danger',
                        ])
                    @else
                        @error('g-recaptcha-response')
                            @include('partials.alert', ['message' => $message, 'messageClass' => 'danger'])
                        @else
                            @include('partials.alert')
                        @enderror
                    @enderror

                    <form method="POST" action="{{ route('login') }}" class="disable-submit">
                        @csrf
                        <div class="d-block mb-2">
                            <label for="username"></label>
                            <div class="input-group">
                                <label for="username" class="input-group-text bg-light">
                                    <i class="fa-solid fa-user"></i>
                                </label>
                                <input id="username" type="text" class="form-control" name="username"
                                    value="{{ old('username') }}" placeholder="Username" required autocomplete="off"
                                    autofocus>
                            </div>
                        </div>
                        <div class="d-block mb-4">
                            <label for="password"></label>
                            <div class="input-group">
                                <label for="password" class="input-group-text bg-light">
                                    <i class="fa-solid fa-key"></i>
                                </label>
                                <input id="password" type="password" class="form-control" name="password"
                                    value="{{ old('password') }}" placeholder="Password" data-icon-open="fa fa-eye"
                                    data-icon-close="fa fa-eye-slash" required autocomplete="off">
                                <label for="password"
                                    class="input-group-text cursor-pointer pwd-view justify-content-center"
                                    data-target="#password" style="width:44px;">
                                    <i class="pwd-icon"></i>
                                </label>
                            </div>
                        </div>
                        {{-- @isLive
                        <div class="d-block mb-4">
                            {!! htmlFormSnippet() !!}
                        </div>
                        @endisLive --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                            <button type="submit" class="btn btn-primary mb-2">Sign In</button>
                            @if (Route::has('password.request'))
                                <a class="text-decoration-none text-end text-light mb-2"
                                    href="{{ route('password.request') }}">
                                    Lupa Password ?
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="processing-overlay" class="show"></div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/app.js?' . $vFile) }}"></script>
    <script>
        $(function() {
            $('.login-box').addClass('ready');
        });
    </script>
</body>

</html>
