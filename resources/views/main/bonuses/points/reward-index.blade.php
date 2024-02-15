@extends('layouts.app-main')

@php
    $statusList = [
        CLAIM_STATUS_PENDING => 'Pending',
        CLAIM_STATUS_FINISH => 'Selesai'
    ];

@endphp

@section('content')
@include('partials.alert')

<select class="form-select form-select-sm d-none" id="reward-status" autocomplete="off">
    <option value="-1">-- Semua --</option>
    @foreach ($statusList as $value => $text)
        <option value="{{ $value }}" @optionSelected($value, $currentStatus)>{{ $text }}</option>
    @endforeach
</select>

<table class="table table-sm table-striped table-nowrap table-hover fs-auto" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Member</th>
            <th class="border-start">Reward</th>
            <th class="border-start">Status</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody></tbody>
</table>
@endsection

@push('includeContent')
    @include('partials.modals.modal', [
        'bsModalId' => 'modal-detail',
        'scrollable' => true,
    ]);
@endpush

@php
    $myDtButtons = [
        [
            'id' => 'btn-refresh',
            'html' => '<i class="fa-solid fa-rotate"></i>',
            'title' => 'Refresh',
            'onclick' => "refreshTable();"
        ],
    ];
@endphp

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker-1.9.0/css/bootstrap-datepicker.standalone.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
@endpush

@push('scripts')

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'sm',
])

<script>
    let table, filterCheck;

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
                url: '{{ route("main.point.claim.datatable") }}',
                data: function(d) {
                    d.reward_status = $('#reward-status').val();
                }
            },
            deferLoading: 50,
            info: true,
            searching: false,
            lengthChange: false,
            pagingType: datatablePagingType,
            language: datatableLanguange,
            buttons: datatableButtons,
            columns: [
                {data: 'created_at'},
                {data: 'member_name', orderable: false},
                {data: 'reward_name', orderable: false},
                {data: 'reward_status', orderable: false, className: 'dt-body-center'},
                {data: 'detail', orderable: false, className: 'dt-body-center'},
            ],
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        
        customizeDatatable();

        const dtControlButton = $('.datatable-controls', dtWrapper);
        const rewardFilter = $('#reward-status');
        const bFirst = $('.datatable-controls #buttons-1', dtWrapper).addClass('me-2');
        const btnFirst = $(':first-child', bFirst);
        btnFirst.append(rewardFilter.removeClass('d-none'));

        rewardFilter.on('change', function(e) {
            refreshTable();
        }).change();

    });
</script>

@endpush