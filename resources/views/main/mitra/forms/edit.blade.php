@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => false,
])

@section('modalTitle', 'Pilih Menu Edit')
@section('stepName', 'edit')

@section('content')
<input type="hidden" name="edit_choice" id="edit-choice" value="">
<div class="d-block mb-2 text-nowrap border-bottom">Pilihan Menu:</div>
<div class="row g-1">
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-primary" title="Edit Data Member" onclick="$('#edit-choice').val('mitra');">
            Data Member
        </button>
    </div>
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-danger" title="Edit Autentikasi" onclick="$('#edit-choice').val('auth');">
            Autentikasi
        </button>
    </div>
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-warning" title="Blokir Member" onclick="$('#edit-choice').val('block');">
            {{ $data->is_login ? 'Blokir' : 'Aktifkan' }} Member
        </button>
    </div>
    {{-- <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-success" title="Edit Jenis dan Level" onclick="$('#edit-choice').val('type');">
            Jenis dan Level
        </button>
    </div> --}}
    {{-- <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-info" title="Edit Referral" onclick="$('#edit-choice').val('referral');">
            Referral (MGR)
        </button>
    </div> --}}
</div>
@endsection