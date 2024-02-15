@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.update', ['userMitra' => $data->id]),
    'showName' => true,
    'showFooter' => true,
    'canSubmit' => hasPermission('main.mitra.edit'),
    'footerClass' => 'flex-row-reverse justify-content-between',
])

@section('modalTitle', 'Edit Referral')
@section('stepName', 'referral')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
<input type="hidden" name="current_referral_id" value="{{ $data->referral_id }}">
<table class="table table-sm table-nowrap table-striped table-hover mb-2" id="table-referral">
    <thead class="bg-gradient-brighten bg-white fs-auto">
        <tr class="text-center">
            <th></th>
            <th class="border-start">Username</th>
            <th class="border-start">Nama</th>
            <th class="border-start">Status</th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>
@endsection

@section('modalScript')
<script>
    $(function() {
        renderTableReferral('{{ $data->referral_id }}');
    });
</script>
@endsection
