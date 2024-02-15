@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
@endphp

@section('modalTitle', 'Pilih Upline')
@section('stepName', 'upline')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
<input type="hidden" name="current_upline_id" value="{{ $data->upline_id }}">
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Member</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<table class="table table-sm table-nowrap table-striped table-hover mb-2" id="table-upline">
    <thead class="bg-gradient-brighten bg-white fs-auto">
        <tr class="text-center">
            <th></th>
            <th class="border-start">Username</th>
            <th class="border-start">Nama</th>
            <th class="border-start">Posisi</th>
            <th class="border-start">Status</th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>
@endsection

@section('modalScript')
<script>
    $(function() {
        renderTableUpline('{{ $data->upline_id }}');
    });
</script>
@endsection