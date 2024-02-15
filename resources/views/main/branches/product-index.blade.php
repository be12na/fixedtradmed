@extends('layouts.app-main')

@section('content')
@include('partials.alert')
<select id="branch-id" class="form-select form-select-sm w-auto mb-1">
    <option value="-1" @optionSelected(-1, $currentBranchId)>-- Pilih Kantor Cabang --</option>
    @foreach ($branches as $row)
        <option value="{{ $row->id }}" @optionSelected($row->id, $currentBranchId)>{{ $row->name }}</option>
    @endforeach
</select>
<table class="table table-sm table-nowrap w-100" id="table">
    <thead class="d-none"><tr><td>Name</td></tr></thead>
    <tbody class="border-top-0"></tbody>
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

@push('styles')
<style>
    .accordion-button {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
    }
    .accordion-button::after {
        margin-left: 0;
        margin-right: 1rem;
    }
</style>
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
    'scrollable' => true,
])
<script>
    let table;

    function formatNumber(value)
    {
        return $.number(value, 0, ',', '.');
    }

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
                url: '{{ route("main.branch.product.datatable") }}',
                data: function(d) {
                    d.branch_id = $('#branch-id').val();
                }
            },
            deferLoading: 50,
            info: false,
            lengthChange: false,
            searching: false,
            pagingType: datatablePagingType,
            lengthMenu: [[1]],
            language: datatableLanguange,
            buttons: datatableButtons,
            columns: [
                {data: 'name', searchable: false, orderable: false, className: 'w-100'}
            ],
        });

        customizeDatatable();

        $('#buttons-1').addClass('me-2');
        const exportContainer = $('#buttons-1').clone().attr({id: 'buttons-export'});
        $('#buttons-1 .dt-buttons').append($('#branch-id'));

        const excelDownlod = $('<button/>').html('<span>Excel</span>').attr({class: 'dt-button buttons-html5', id: 'btn-download-excel', type: 'button'});
        exportContainer.find('.dt-buttons').first().append(excelDownlod);
        $('#buttons-1').after(exportContainer);

        excelDownlod.on('click', function(e) {
            let url = '{{ route("main.branch.product.download.excel") }}?branch_id=' + $('#branch-id').val();
            window.open(url);
        });

        $('#branch-id').on('change', function() {
            table.ajax.reload();
        }).change();
    });
</script>
@endpush
