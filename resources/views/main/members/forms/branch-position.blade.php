@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
    $currentMgrType = $data->manager_type;
    $currentPositionExt = $data->position_ext;
@endphp

@section('modalTitle', 'Jabatan')
@section('stepName', 'branch-position')
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
    <label class="d-block required">Jenis Manager</label>
    <select name="manager_type" id="manager_type" class="form-select">
        @foreach (USER_BRANCH_MANAGER_TYPES as $typeId => $typeName)
            <option value="{{ $typeId }}" @optionSelected($typeId, $currentMgrType)>{{ $typeName ?? '-- Pilih Jenis Manager --' }}</option>
        @endforeach
    </select>
</div>
<div class="d-block">
    <label class="d-block required">Posisi Cabang</label>
    <select name="position_ext" id="position_ext" class="form-select">
        <option value="">-- Pilih Posisi --</option>
        @foreach (app('appStructure')->getExternalManagerOptions() as $idExt => $nameExt)
            <option value="{{ $idExt }}" @optionSelected($idExt, $currentPositionExt)>{{ $nameExt }}</option>
        @endforeach
    </select>
</div>
@endsection