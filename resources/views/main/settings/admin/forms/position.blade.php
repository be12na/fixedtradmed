@extends('main.settings.admin.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
    $currentAdminId = $position->user_type . '.' . $position->division_id;
@endphp

@section('modalTitle', 'Posisi Administrator')
@section('stepName', 'position')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Administrator</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<div class="row g-2">
    <div class="col-12">
        <label class="d-block required">Posisi</label>
        <select class="form-select" name="types" autocomplete="off">
            @foreach ($admins as $key => $value)
                @if (is_array($value))
                    <optgroup label="{{ $value[0] }}">
                        @foreach ($value[1] as $idValue => $nameValue)
                            <option value="{{ $idValue }}" @optionSelected($idValue, $currentAdminId)>{{ $nameValue }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $key }}" @optionSelected($key, $currentAdminId)>{{ $value }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>
@endsection