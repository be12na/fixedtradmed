@extends('main.mitra.modal-form', [
    'disableSubmit' => false,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@section('modalTitle', 'Konfirmasi')
@section('stepName', 'confirm')
@section('submitIcon', 'fa-save')
@section('submitText',  isset($values['block_member']) ? ($data->is_login ? 'Blokir' : 'Aktifkan') : 'Simpan')

@section('content')
<div class="d-block w-100 border overflow-x-auto">
    <table class="table table-sm table-nowrap table-striped mb-2">
        @if (isset($values['name']))
            <tr>
                <td class="fw-bold">Nama</td>
                <td class="text-end">{{ $values['name'] }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Email</td>
                <td class="text-end">{{ $values['email'] }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Handphone</td>
                <td class="text-end">{{ $values['phone'] }}</td>
            </tr>
        @endif
        @if (isset($values['username']))
            <tr>
                <td class="fw-bold">Username</td>
                <td class="text-end">{{ $values['username'] }}</td>
            </tr>
            @if (isset($values['password']))
                <tr>
                    <td class="fw-bold">Password</td>
                    <td class="text-end">{{ $values['password'] }}</td>
                </tr>
            @endif
        @endif
        @if (isset($values['mitra_type']))
            <tr>
                <td class="fw-bold">Paket</td>
                <td class="text-end">{{ \Arr::get(MITRA_TYPES, $values['mitra_type']) }}</td>
            </tr>
        @endif
        @if (isset($values['referral_id']))
            <tr>
                <td class="fw-bold">Referral</td>
                <td class="text-end"></td>
            </tr>
            <tr>
                <td class="fw-bold ps-4">Lama</td>
                <td class="text-end">{{ $values['old_referral'] }}</td>
            </tr>
            <tr>
                <td class="fw-bold ps-4">Baru</td>
                <td class="text-end">{{ $values['new_referral'] }}</td>
            </tr>
        @endif
        @if (isset($values['block_member']))
            <tr>
                <td class="fw-bold">Nama</td>
                <td class="text-end">{{ $data->name }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Email</td>
                <td class="text-end">{{ $data->email }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Handphone</td>
                <td class="text-end">{{ $data->phone }}</td>
            </tr>
            <tr>
                @php
                    $actionText = $data->is_login ? 'Blokir' : 'Aktifkan';
                @endphp
                <td class="fw-bold text-center" colspan="2">
                    {{ $actionText . ($data->is_login ? '' : ' kembali') }} member tersebut ?
                </td>
            </tr>
        @endif
    </table>
</div>
@endsection