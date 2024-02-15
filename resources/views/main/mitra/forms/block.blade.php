@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@php
    $actionText = $data->is_login ? 'Blokir' : 'Aktifkan';
@endphp

@section('modalTitle', $actionText . ' Member')
@section('stepName', 'block')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
<input type="hidden" name="block_member" value="{{ $data->is_login ? 1 : 0 }}">
<div>{{ $actionText . ($data->is_login ? '' : ' kembali') }} member tersebut ?</div>
@endsection