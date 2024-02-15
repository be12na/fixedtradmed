@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@section('modalTitle', 'Edit Data')
@section('stepName', 'mitra')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
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