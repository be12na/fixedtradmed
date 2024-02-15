@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
@endphp

@section('modalTitle', 'Data')
@section('stepName', 'member')
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
        <label class="d-block required">Nama</label>
        <input type="text" class="form-control" name="name" id="input-name" value="{{ $data->name }}" placeholder="Nama" autocomplete="off" required autofocus>
    </div>
    <div class="col-md-6">
        <label class="d-block required">Email</label>
        <input type="email" class="form-control" name="email" id="email" value="{{ $data->email }}" placeholder="Email" autocomplete="off" required>
    </div>
    <div class="col-md-6">
        <label class="d-block required">Handphone</label>
        <input type="text" class="form-control" name="phone" id="phone" value="{{ $data->phone }}" placeholder="Handphone" autocomplete="off" required>
    </div>
</div>
@endsection