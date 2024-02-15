@extends('layouts.app-mitra')

@section('content')
@include('partials.alert')

<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Nama</th>
            <th class="border-start">Username</th>
            <th class="border-start">Paket</th>
            <th class="border-start">Email</th>
            <th class="border-start">No. HP</th>
            {{-- <th class="border-start">Toko</th> --}}
            {{-- <th class="border-start">Status</th> --}}
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
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
                url: '{{ route("mitra.myMember.datatable") }}'
            },
            deferLoading: 50,
            info: true,
            lengthChange: false,
            search: {
                return: true
            },
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            buttons: datatableButtons,
            columns: [
                {data: 'name'},
                {data: 'username'},
                {data: 'package_id', searchable: false, orderable: false, className: 'dt-body-center'},
                {data: 'email'},
                {data: 'phone'},
                // {data: 'market_name'},
                // {data: 'status', searchable: false, orderable: false, className: 'dt-body-center'},
                {data: 'view', searchable: false, orderable: false, className: 'dt-body-center'},
            ],
        });
        
        customizeDatatable();
        
        refreshTable();
    });
</script>
@endpush
