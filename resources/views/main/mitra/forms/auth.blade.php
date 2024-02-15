@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@section('modalTitle', 'Autentikasi')
@section('stepName', 'auth')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
<div class="row g-2">
    <div class="col-12">
        <label class="d-block required">Username</label>
        <input type="text" class="form-control" name="username" id="username" value="{{ $data->username }}" placeholder="Username" autocomplete="off" autofocus>
    </div>
    <div class="col-sm-6">
        <label class="d-block">Password</label>
        <input type="password" class="form-control" name="password" id="password" placeholder="Password" autocomplete="off">
    </div>
    <div class="col-sm-6">
        <label class="d-block">Ketik Ulang Password</label>
        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" placeholder="Password" autocomplete="off">
    </div>
</div>
@endsection