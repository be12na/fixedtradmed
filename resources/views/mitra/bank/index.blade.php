@extends('layouts.app-mitra')

@section('content')
@include('partials.alert')

@php
    $canCreate = true;
    $canEdit = true;
    $canAction = $canEdit;
@endphp

<table class="table table-sm table-nowrap table-striped table-hover small" id="table">
    <thead class="bg-gradient-brighten bg-white">
        <tr class="text-center">
            <th>No</th>
            <th class="border-start">Bank</th>
            <th class="border-start">Nama Rekening</th>
            <th class="border-start">No. Rekening</th>
            <th class="border-start">Status</th>
            @if ($canAction)
                <th class="border-start"></th>
            @endif
        </tr>
    </thead>
    <tbody>
        @php $noUrut = 1; @endphp
        @foreach ($banks as $bank)
            <tr>
                <td>{{ $noUrut++ }}</td>
                <td>{{ $bank->bank_name }}</td>
                <td>{{ $bank->account_name }}</td>
                <td>{{ $bank->account_no }}</td>
                <td class="text-center">@contentCheck($bank->is_active)</td>
                @if ($canAction)
                    <td>
                        @if ($canEdit)
                            <button type="button" class="btn btn-sm btn-outline-success btn-data-control" data-modal-url="{{ route('mitra.bank.edit', ['memberBank' => $bank->id]) }}" title="Edit" data-bs-toggle="modal" data-bs-target="#my-modal">
                                <i class="fa-solid fa-pencil-alt"></i>
                            </button>
                        @endif
                    </td>
                @endif
            </tr>            
        @endforeach
    </tbody>
</table>
@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
@endpush

@push('scripts')

@php
    $myDtButtons = [];
    if ($canCreate) {
        $myDtButtons[] = [
            'id' => 'btn-tambah',
            'html' => 'Tambah',
            'data-modal-url' => route('mitra.bank.create'),
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#my-modal',
            'class' => 'btn-data-control'
        ];
    }

    $columnDefs = [];
    if ($canAction) {
        $columnDefs[] = ['orderable' => false, 'targets' => [5]];
    }
    $columnDefs = json_encode($columnDefs);
@endphp

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'sm',
])

<script>
    $(function() {
        const datatableColumnDefs = {!! $columnDefs !!};
        $('#table').DataTable({
            dom: datatableDom,
            info: false,
            lengthChange: false,
            searching: false,
            paging: false,
            language: datatableLanguange,
            buttons: datatableButtons,
            columnDefs: datatableColumnDefs
        });

        customizeDatatable();
    });
</script>
@endpush
