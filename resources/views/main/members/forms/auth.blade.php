@extends('main.members.modal-form')

@php
    $isEdit = $hasData = !empty($data);
    $data = optional($data);
@endphp

@section('modalTitle', 'Autentikasi')
@section('stepName', 'auth')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Member</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<div class="row g-2">
    <div class="col-12">
        <label class="d-block required">Username</label>
        <input type="text" class="form-control" name="username" id="username" value="{{ $data->username }}" placeholder="Username" autocomplete="off" autofocus>
    </div>
    <div class="col-sm-6">
        <label class="d-block @if(!$isEdit) required @endif">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off">
    </div>
    <div class="col-sm-6">
        <label class="d-block @if(!$isEdit) required @endif">Ketik Ulang Password</label>
        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Password" autocomplete="off">
    </div>
</div>
@endsection