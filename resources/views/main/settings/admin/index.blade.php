@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.settings.admin.create');
    $canEdit = hasPermission('main.settings.admin.edit');
@endphp

@section('content')
@include('partials.alert')
<select class="form-select form-select-sm w-auto me-2 mb-1" id="type-divisi" autocomplete="off">
    <option value="0.0">-- Semua --</option>
    @foreach ($admins as $key => $value)
        @if (is_array($value))
            <optgroup label="{{ $value[0] }}">
                @foreach ($value[1] as $idValue => $nameValue)
                    <option value="{{ $idValue }}" @optionSelected($idValue, $currentAdminId)>{{ $nameValue }}</option>
                @endforeach
            </optgroup>
        @else
            <option value="{{ $key }}" @optionSelected($key, $currentAdminId)>{{ $value }}</option>
        @endif
    @endforeach
</select>

<table class="table table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Username</th>
            <th class="border-start">Nama</th>
            <th class="border-start">Status</th>
            @if ($canEdit)
            <th class="border-start"></th>
            @endif
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

    $columns = [
        ['data' => 'username'],
        ['data' => 'name'],
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
            'data-modal-url' => route('main.settings.admin.create'),
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
                url: '{{ route("main.settings.admin.datatable") }}',
                data: function(d) {
                    d.type_division = $('#type-divisi').val();
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
            order: [[1, 'asc']],
            columns: {!! $columns !!}
        });

        customizeDatatable();

        $('#buttons-1').addClass('me-2');
        const exportContainer = $('#buttons-1 .dt-buttons');
        exportContainer.append($('#type-divisi'));

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

        $('#type-divisi').on('change', function() {
            refreshTable();
        }).change();
    });
</script>
@endpush
