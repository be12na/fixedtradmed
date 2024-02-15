@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.member.create');
    $canEdit = hasPermission('main.member.edit');
@endphp

@section('content')
@include('partials.alert')
<select class="form-select form-select-sm w-auto me-2 mb-1" id="position-id" autocomplete="off">
    <option value="-1">-- Semua Posisi --</option>
    @foreach (app('appStructure')->getAllData(true) as $row)
        <option value="{{ $row->id }}" @optionSelected($row->id, $currentPositionId)>{{ $row->name }}</option>
    @endforeach
</select>

<table class="table table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Username</th>
            <th class="border-start">Nama</th>
            <th class="border-start">Email</th>
            <th class="border-start">Handphone</th>
            <th class="border-start">Upline</th>
            <th class="border-start">Cabang</th>
            <th class="border-start">Status</th>
            @if ($canEdit)
            <th class="border-start"></th>
            @endif
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>
@endsection

@if ($canCreate || $canEdit)
    @push('includeContent')
        @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
    @endpush
@endif

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
        ['data' => 'username'],
        ['data' => 'name'],
        ['data' => 'email'],
        ['data' => 'phone'],
        ['data' => 'upline_name'],
        ['data' => 'branch_name', 'orderable' => false],
        ['data' => 'user_status', 'orderable' => false, 'searchable' => false, 'className' => 'dt-body-center'],
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
            'data-modal-url' => route('main.member.create'),
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

    function renderTableUpline(currentUpluneId)
    {
        let url = '{{ route("main.member.datatable") }}?modal_upline=1';
        if ((currentUpluneId != undefined) && (currentUpluneId != '')) {
            url = url + '&current_upline=' + currentUpluneId;
        }

        $('#table-upline').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: url
            },
            info: false,
            lengthChange: false,
            search: {
                return: true
            },
            pagingType: datatablePagingType,
            lengthMenu: datatableLengMenu,
            language: datatableLanguange,
            order: [[1, 'asc']],
            buttons: [],
            columns: [
                {data: 'id', sortable: false, searchable: false, className: 'dt-body-center'},
                {data: 'username'},
                {data: 'name'},
                {data: 'position_int', sortable: false, searchable: false},
                {data: 'user_status', sortable: false, searchable: false, className: 'dt-body-center'},
            ]
        });

        $('#table-upline_wrapper .dataTables_processing').addClass('text-success').removeClass('card').html(spinerProcessing);

        const dtc = $('#table-upline_wrapper .datatable-controls').removeClass('d-flex flex-column flex-sm-row');
        const childOfDtc = $('#table-upline_wrapper .datatable-controls > div');
        $('#table-upline_filter.dataTables_filter').prependTo(dtc);
        childOfDtc.remove();
    }

    $(function() {
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.member.datatable") }}',
                data: function(d) {
                    d.position_id = $('#position-id').val();
                }
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
            order: [[0, 'asc']],
            columns: {!! $columns !!}
        });

        customizeDatatable();

        $('#buttons-1').addClass('me-2');
        const exportContainer = $('#buttons-1 .dt-buttons');
        const excelDownlod = $('<button/>').html('<span>Excel</span>').attr({class: 'dt-button buttons-html5', id: 'btn-download-excel', type: 'button'});
        
        exportContainer.append($('#position-id')).append(excelDownlod);

        excelDownlod.on('click', function(e) {
            let url = '{{ route("main.member.download.excel") }}?position_id=' + $('#position-id').val();
            window.open(url);
        });
        
        $('#position-id').on('change', function() {
            refreshTable();
        }).change();

        $(document).on('submit', '#my-modal form.modal-content.disable-submit', function(e) {
            const frm = $(this);
            const data = frm.serialize();
            const url = frm.attr('action');
            const msg = $(frm.data('alert-container'));
            showMainProcessing();
            $.post({
                url: url,
                data: data
            }).done(function(respon) {
                $('#my-modal .modal-dialog').empty().html(respon);
            }).fail(function(respon) {
                msg.empty().html(respon.responseText);
            }).always(function(respon) {
                stopMainProcessing();
            });

            return false;
        });
    });
</script>
@endpush
