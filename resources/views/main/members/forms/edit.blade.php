@extends('main.members.modal-form')

@section('modalTitle', 'Pilih Menu Edit')
@section('stepName', 'edit')

@section('content')
<input type="hidden" name="edit_choice" id="edit-choice" value="">
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Member</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
<div class="d-block mb-2 text-nowrap border-bottom">Pilihan Menu:</div>
<div class="row g-1">
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-primary" title="Edit Data Member" onclick="$('#edit-choice').val('member');">
            Data Member
        </button>
    </div>
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-danger" title="Edit Autentikasi Member" onclick="$('#edit-choice').val('auth');">
            Autentikasi
        </button>
    </div>
    
    @if ($data->position_int > USER_INT_GM)
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-info" title="Edit Upline" onclick="$('#edit-choice').val('upline');">
            Upline
        </button>
    </div>
    @endif

    @if ($data->position_int >= USER_BRANCH_MANAGER)
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-warning" title="Edit Upline" onclick="$('#edit-choice').val('branch');">
            Cabang
        </button>
    </div>
    @endif
    @if ($data->position_int > USER_INT_GM)
    <div class="col-6">
        <button type="submit" class="d-block h-100 w-100 btn btn-success" title="Upgrade Posisi" onclick="$('#edit-choice').val('upgrade-position');">
            Upgrade Posisi
        </button>
    </div>
    @endif
</div>
@endsection