@extends('main.settings.admin.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
@endphp

@section('modalTitle', 'Konfirmasi')
@section('stepName', 'confirm')
@section('submitIcon', 'fa-save')
@section('submitText', 'Simpan')

@section('content')
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Administrator</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<div class="d-block w-100 border overflow-x-auto">
    <table class="table table-sm table-nowrap mb-2">
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
        @if (isset($values['types']))
            <tr>
                <td class="fw-bold">Posisi</td>
                <td class="text-end">{{ $values['position_name'] }}</td>
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
    </table>
</div>
@endsection