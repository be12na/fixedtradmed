@extends('main.mitra.modal-form', [
    'disableSubmit' => true,
    'submitUrl' => route('main.mitra.edit', ['userMitra' => $data->id]),
    'showName' => false,
    'canSubmit' => hasPermission('main.mitra.edit'),
])

@section('modalTitle', 'Detail')
@section('submitIcon', 'fa-pencil')
@section('submitText', 'Edit')

@section('content')
    <div class="d-block w-100 border overflow-x-auto">
        <table class="table table-sm table-striped table-nowrap mb-2 fs-auto">
            <tbody>
                <tr>
                    <td>Username</td>
                    <td class="text-end">{{ $data->username }}</td>
                </tr>
                <tr>
                    <td>Nama</td>
                    <td class="text-end">{{ $data->name }}</td>
                </tr>
                <tr>
                    <td>Referral</td>
                    <td class="text-end">{{ $data->referral_name }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td class="text-end">{{ $data->email }}</td>
                </tr>
                <tr>
                    <td>Handphone</td>
                    <td class="text-end">{{ $data->phone }}</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td class="text-end {{ memberStatusColor($data) }}">{{ memberStatusText($data) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
