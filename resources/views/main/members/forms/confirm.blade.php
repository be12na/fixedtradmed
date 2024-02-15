@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
@endphp

@section('modalTitle', 'Konfirmasi')
@section('stepName', 'confirm')
@section('submitIcon', 'fa-save')
@section('submitText', 'Simpan')

@section('content')
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Member</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<div class="d-block w-100 border overflow-x-auto">
    <table class="table table-sm table-nowrap mb-2">
        @if (isset($values['name']))
            <tr>
                <td class="fw-bold">Nama</td>
                <td class="text-end">{{ $values['name'] }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Email</td>
                <td class="text-end">{{ $values['email'] }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Handphone</td>
                <td class="text-end">{{ $values['phone'] }}</td>
            </tr>
        @endif
        @if (isset($values['username']))
            <tr>
                <td class="fw-bold">Username</td>
                <td class="text-end">{{ $values['username'] }}</td>
            </tr>
            @if (isset($values['password']))
                <tr>
                    <td class="fw-bold">Password</td>
                    <td class="text-end">{{ $values['password'] }}</td>
                </tr>
            @endif
        @endif
        @if (isset($upline) && !empty($upline))
            <tr>
                <td class="fw-bold">Upline</td>
                <td class="text-end">{{ $upline->name }}</td>
            </tr>
        @endif
        @if (isset($positionInt))
            <tr>
                <td class="fw-bold">Posisi</td>
                <td class="text-end">{{ $positionInt->name }}</td>
            </tr>
        @endif
        {{-- @if (isset($managerType))
            <tr>
                <td class="fw-bold">Jenis Manager</td>
                <td class="text-end">{{ $managerType }}</td>
            </tr>
        @endif --}}
        {{-- @if (isset($positionExt))
            <tr>
                <td class="fw-bold">Posisi Cabang</td>
                <td class="text-end">{{ $positionExt }}</td>
            </tr>
        @endif --}}
        @if (isset($branches) && $branches->isNotEmpty())
            <tr>
                <td class="fw-bold">Kantor Cabang</td>
                <td class="text-end">
                    @php
                        $indexBranch = 0;
                    @endphp
                    @foreach ($branches as $branch)
                        <div class="d-flex justify-content-end text-end">
                            <div>{{ $branch->name }}</div>
                            @if ($values['position_id'] == USER_INT_MGR)
                                @php
                                    $posName = app('appStructure')->codeById(false, $values['branch_positions'][$indexBranch]);
                                    $typeCode = \Arr::get(USER_BRANCH_MANAGER_CODES, $values['branch_types'][$indexBranch]);
                                @endphp
                                <div class="ms-2 text-primary">
                                    ({{ $posName }} - {{ $typeCode }})
                                </div>
                            @endif
                        </div>
                        @php
                            $indexBranch++;
                        @endphp
                    @endforeach
                </td>
            </tr>
        @endif
    </table>
</div>
@endsection