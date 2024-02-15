@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@section('modalTitle', 'Edit Jenis dan Level')
@section('stepName', 'type')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
<div class="row g-2">
    <div class="col-sm-6">
        <label class="d-block required">Paket</label>
        <select class="form-select" name="mitra_type" id="mitra-type">
            @foreach (MITRA_TYPES as $key => $value)
                <option value="{{ $key }}" @optionSelected($key, $data->mitra_type)>{{ $value }}</option>
            @endforeach
        </select>
    </div>
</div>
@endsection