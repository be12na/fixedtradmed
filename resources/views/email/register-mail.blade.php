<div style="padding:1rem; background-color:#dee2e6; font-size:0.85rem; line-height:1.5;">
    <div style="background-color:#f8f9fa; padding:1.5rem;">
        <div style="margin-bottom:1rem; text-align:center;">
            <img src="{{ asset('images/logo-main.png') }}" style="height:40px; width:auto; max-width:80%;" alt="{{ config('app.name') }}">
        </div>
        <div style="text-align:center; font-size:1.25rem;font-weight:600;margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:2px solid #212529">
            Informasi Pendaftaran
        </div>
        <div style="margin-bottom: 1rem;">
            <div>Selamat datang <b>{{ $user->name }}</b>,</div>
            <div>Terima kasih sudah bergabung dengan <b>{{ config('app.name') }}</b>. Berikut adalah informasi data yang anda gunakan untuk mendaftar:</div>
        </div>
        <div style="font-weight:600;">Data Personal</div>
        <div style="display:flex; flex-wrap:nowrap;">
            <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Nama Lengkap</span>
            <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
            <span style="flex:1 1 auto;">{{ $user->name }}</span>
        </div>
        <div style="display:flex; flex-wrap:nowrap;">
            <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Email</span>
            <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
            <span style="flex:1 1 auto;">{{ $user->email }}</span>
        </div>
        <div style="display:flex; flex-wrap:nowrap;">
            <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Handphone</span>
            <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
            <span style="flex:1 1 auto;">{{ $user->phone }}</span>
        </div>
        <div style="display:flex; flex-wrap:nowrap;">
            <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Username</span>
            <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
            <span style="flex:1 1 auto;">{{ $user->username }}</span>
        </div>
        @if ($user->referral)
            <div style="margin-top: 1rem; font-weight:600;">Data Referral</div>
            <div style="display:flex; flex-wrap:nowrap;">
                <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Nama Lengkap</span>
                <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
                <span style="flex:1 1 auto;">{{ $user->referral->name }} ({{ $user->referral->username }})</span>
            </div>
            <div style="display:flex; flex-wrap:nowrap;">
                <span style="display:inline-block; flex-shrink:0; width: 150px; max-width: 40%; margin-right:0.5rem;">Handphone</span>
                <span style="flex-shrink:0; margin-right:0.5rem;">:</span>
                <span style="flex:1 1 auto;">{{ $user->referral->phone ?? '-' }}</span>
            </div>
        @endif
    </div>
</div>