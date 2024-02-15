@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.settings.reward.create');
    $canEdit = hasPermission('main.settings.reward.edit');
@endphp

@section('content')
@include('partials.alert')
<table class="table table-nowrap table-striped table-hover fs-auto" id="table">
    <thead class="bg-gradient-brighten bg-white">
        <tr class="text-center">
            <th>Poin</th>
            <th class="border-start">Reward</th>
            {{-- <th class="border-start">Aktif</th> --}}
            @if ($canEdit)
            <th class="border-start"></th>
            @endif
        </tr>
    </thead>
    <tbody></tbody>
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

    $columns = [
        ['data' => 'point', 'className' => 'dt-body-center'],
        ['data' => 'reward'],
        // ['data' => 'status', 'orderable' => false, 'searchable' => false, 'className' => 'dt-body-center'],
    ];

    if ($canEdit) {
        $columns[] = [
            'data' => 'view', 
            'searchable' => false, 
            'orderable' => false, 
            'className' => 'dt-body-center', 
            'exportable' => false, 
            'printable' => false
        ];
    }

    $columns = json_encode($columns);

    if ($canCreate) {
        $myDtButtons[] = [
            'id' => 'btn-tambah',
            'html' => 'Tambah',
            'data-bs-toggle' => 'modal',
            'data-bs-target' => '#my-modal',
            'data-modal-url' => route('main.settings.reward.create'),
        ];
    }

    $myDtButtons[] = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => "refreshTable();"
    ];
@endphp

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'sm',
])
<script>
    let table;

    function refreshTable()
    {
        table.ajax.reload();
    }

    $(function() {
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.settings.reward.datatable") }}',
            },
            deferLoading: 50,
            info: true,
            lengthChange: false,
            searching: false,
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'asc']],
            columns: {!! $columns !!}
        });

        customizeDatatable();

        refreshTable();
    });
</script>
@endpush
