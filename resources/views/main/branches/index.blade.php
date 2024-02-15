@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.branch.list.create');
    $canEdit = hasPermission('main.branch.list.edit');
    $canAction = $canEdit;
@endphp

@section('content')
@include('partials.alert')
<div class="d-block">
    <table class="table table-sm table-nowrap table-striped table-hover" id="table">
        <thead class="bg-gradient-brighten bg-white small">
            <tr class="text-center">
                <th>No</th>
                <th class="border-start">Kode</th>
                <th class="border-start">Nama</th>
                <th class="border-start">Distributor</th>
                <th class="border-start">@if ($isAppV2) Zona @else Wilayah @endif</th>
                <th class="border-start">Alamat</th>
                <th class="border-start">Stock</th>
                <th class="border-start">Status</th>
                @if ($canAction)
                <th class="border-start">&nbsp;</th>
                @endif
            </tr>
        </thead>
        <tbody class="small">
            @php $noUrut = 1; @endphp
            @foreach ($branches as $row)
                <tr>
                    <td>{{ $noUrut++ }}</td>
                    <td>{{ $row->code }}</td>
                    <td>{{ $row->name }}</td>
                    <td>
                        @if ($row->distributors->isNotEmpty())
                            <ul class="mb-0 ps-3">
                            @foreach ($row->distributors as $rowDist)
                                <li>{{ $rowDist->manager->name }}</li>
                            @endforeach
                            </ul>
                        @endif
                    </td>
                    <td>{{ $isAppV2 ? ($row->zone_name_v2 ?? '-') : ($row->zone_name ?? '-') }}</td>
                    <td class="text-wrap">{{ $row->address }} {{ $row->pos_code }}</td>
                    <td class="text-center">
                        @contentCheck($row->is_stock)
                    </td>
                    <td class="text-center">
                        @contentCheck($row->is_active)
                    </td>
                    @if ($canAction)
                        <td class="text-center">
                            @if ($canEdit)
                                <button type="button" class="btn btn-sm btn-outline-success btn-data-control" data-modal-url="{{ route('main.branch.list.edit', ['branch' => $row->id]) }}" title="Edit" data-bs-toggle="modal" data-bs-target="#my-modal">
                                    <i class="fa-solid fa-pencil-alt"></i>
                                </button>
                            @endif
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal'])
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
            'data-modal-url' => route('main.branch.list.create'),
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#my-modal',
            'class' => 'btn-data-control'
        ];
    }

    $columnDefs = [];
    if ($canAction) {
        $columnDefs[] = ['orderable' => false, 'targets' => [7]];
    }
    $columnDefs = json_encode($columnDefs);
@endphp

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'sm',
])
<script>
    const datatableColumnDefs = {!! $columnDefs !!};
    $(function() {
        $('#table').DataTable({
            dom: datatableDom,
            info: true,
            lengthChange: false,
            stateSave: true,
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            columnDefs: datatableColumnDefs
        });

        customizeDatatable();
    });
</script>
@endpush
