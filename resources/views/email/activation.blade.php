<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktifasi Akun</title>
    <style>
        .tombol-aktifasi {
            display: inline-block;
            padding: 0.5rem 1rem;
            color: #343a40;
            background: rgba(255, 236, 128, 0.75) linear-gradient(to bottom, rgba(255, 255, 255, 0.35), rgba(255, 255, 255, 0));
            border: 1px solid rgba(255, 236, 128, 0.5);
            border-radius: 0.25rem;
            text-decoration: none !important;
            transition: color 0.35s, background-color 0.35s, box-shadow 0.35s;
        }
        .tombol-aktifasi:hover,
        .tombol-aktifasi:focus {
            color: #495057;
            background-color: rgba(255, 236, 128, 0.825);
        }
        .tombol-aktifasi:focus {
            box-shadow: 0 0 1rem rgba(255, 236, 128, 0.5);
        }
        .tombol-aktifasi:active {
            color: #6c757d;
            background-color: rgba(255, 236, 128, 0.9);
        }
    </style>
</head>
<body style="font-size: 1rem; line-height: 1.5; background-color: #fff;">
    <div style="display: flex; justify-content: center; padding: 0.5rem;">
        <div style="display: block; padding: 2rem; border: 1px solid #003300; background-color: #003300; color: #f8f9fa; border-radius: 1rem;">
            <div style="margin-bottom: 1rem; text-align: center;">
                <img alt="LOGO" src="{{ asset('images/logo-main.png') }}" style="height: 40px; width: auto;">
            </div>
            <div style="margin-bottom: 0.5rem;">Selamat datang <span style="font-weight: 600;">Tatang</span></div>
            <div>Berikut adalah data akun anda sebagai {{ $asUser }}:</div>
            <ul style="list-style: none; margin-bottom: 1rem;">
                <li>
                    <div style="display: flex; flex-wrap: nowrap; white-space: nowrap;">
                        <div style="flex-basis: 120px; font-weight: 600;">Username</div>
                        <div style="flex: 0 0 2px; margin-right: .25rem;">:</div>
                        <div style="flex: 1 1 auto; white-space: normal;">{{ $data->username }}</div>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap; white-space: nowrap;">
                        <div style="flex-basis: 120px; font-weight: 600;">Nama</div>
                        <div style="flex: 0 0 2px; margin-right: .25rem;">:</div>
                        <div style="flex: 1 1 auto; white-space: normal;">{{ $data->name }}</div>
                    </div>
                    <div style="display: flex; flex-wrap: nowrap; white-space: nowrap;">
                        <div style="flex-basis: 120px; font-weight: 600;">Handphone</div>
                        <div style="flex: 0 0 2px; margin-right: .25rem;">:</div>
                        <div style="flex: 1 1 auto; white-space: normal;">{{ $data->phone }}</div>
                    </div>
                </li>
            </ul>
            <div style="margin-bottom: 1rem;">
                Silahkan klik tombol di bawah untuk mengaktifkan akun anda
            </div>
            <div style="display: flex; justify-content: center; text-align: center;">
                <a class="tombol-aktifasi" href="{{ route('activate', ['username_activation' => $data->username]) }}" style="display: flex; flex-direction: column;">
                    <div>
                        <img alt="LOGO" src="{{ asset('images/logo.png') }}" style="height: 20px; width: auto;">
                    </div>
                    <span style="text-align: center;">Aktifkan</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>