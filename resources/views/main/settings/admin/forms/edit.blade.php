@extends('main.settings.admin.modal-form')

@section('modalTitle', 'Pilih Menu Edit')
@section('stepName', 'edit')

@section('content')
<input type="hidden" name="edit_choice" id="edit-choice" value="">
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Administrator</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
<div class="d-block mb-2 text-nowrap border-bottom">Pilihan Menu:</div>
<div class="row g-1">
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-primary" title="Edit Data Administrator" onclick="$('#edit-choice').val('personal');">
            Data
        </button>
    </div>
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-danger" title="Edit Autentikasi" onclick="$('#edit-choice').val('auth');">
            Autentikasi
        </button>
    </div>
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-warning" title="Edit Posisi Administrator" onclick="$('#edit-choice').val('position');">
            Posisi
        </button>
    </div>
</div>
@endsection