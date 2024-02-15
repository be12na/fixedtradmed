<div style="padding:1rem; background-color:#dee2e6; font-size:0.85rem; line-height:1.5;">
    <div style="background-color:#f8f9fa; padding:1.5rem;">
        <div style="margin-bottom:1rem; text-align:center;">
            <img src="{{ asset('images/logo-main.png') }}" style="height:40px; width:auto; max-width:80%;"
                alt="{{ config('app.name') }}">
        </div>
        <div
            style="text-align:center; font-size:1.25rem;font-weight:600;margin-bottom:1rem;padding-bottom:0.5rem;border-bottom:2px solid #212529">
        </div>
        <div style="margin-bottom: 1rem;">
            <div>Notifikasi Penarikan Bonus,,</div>
            <div>Selamat, sahabat <b>{{ config('app.name') }}</b> / <b>{{ $user->name }}</b></div>
            <div>Penarikan Bonus {{ $bonusType }} Anda sebesar: Rp <b>@formatNumber($bonus)</b>,-</div>
            <div>telah berhasil diproses.</div>
        </div>
        <div style="margin-bottom: 1rem;">Semoga menjadi rejeki yang berkah, besar, bermanfaat bagi Anda, Keluarga, dan
            Banyak Orang</div>
        <div>Terima Kasih</div>
    </div>
</div>
