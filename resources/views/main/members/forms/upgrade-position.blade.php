@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
    $currentMgrType = $data->manager_type;
    $currentPositionExt = $data->position_ext;
@endphp

@section('modalTitle', 'Jabatan')
@section('stepName', 'upgrade-position')
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
<div class="d-block mb-2">
    <label class="d-block required">Posisi Baru</label>
    <select name="position_int" id="position_int" class="form-select">
        <option value="">-- Pilih Posisi --</option>
        @foreach ($positions as $position)
            <option value="{{ $position->id }}">{{ $position->name }}</option>
        @endforeach
    </select>
</div>
@endsection