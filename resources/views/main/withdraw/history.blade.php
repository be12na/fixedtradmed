@extends('layouts.app-main')

@php
    $isCashback = isset($isCashback) ? $isCashback === true : false;
@endphp

@section('content')
    @include('partials.alert')
    <div class="row g-2 mb-2" id="bonus-filter">
        <div class="col-md-5 col-lg-4 d-flex">
            <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
                <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total</div>
                <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1"
                    id="total-bonusan">0</div>
            </div>
        </div>
        <div class="col-md-7 col-lg-8 d-flex">
            @include('main.bonuses.menu.date-filter')
        </div>
    </div>
    <table class="table table-sm table-striped table-nowrap table-hover fs-auto" id="table">
        <thead class="bg-gradient-brighten bg-white small">
            <tr class="text-center">
                <th class="border-start">Tanggal</th>
                <th class="border-start">Kode</th>
                <th class="border-start">Jns Bonus</th>
                <th class="border-start">Member</th>
                <th></th>
                <th class="border-start">Bank</th>
                <th></th>
                <th></th>
                <th class="border-start">Total Bonus</th>
                <th class="border-start">Admin Fee</th>
                <th class="border-start">Total Transfer</th>
                <th class="border-start">Status</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <div class="d-none d-flex flex-nowrap align-items-center me-2 mb-2" id="filter-status">
        <span class="me-1">Status</span>
        <select id="select-status" class="form-select form-select-sm">
            <option value="-1" @if ($status == -1) selected @endif>-- Semua --</option>
            <option value="{{ CLAIM_STATUS_PENDING }}" @if ($status == CLAIM_STATUS_PENDING) selected @endif>
                {{ Arr::get(CLAIM_STATUS_LIST, CLAIM_STATUS_PENDING) }}</option>
            <option value="{{ CLAIM_STATUS_FINISH }}" @if ($status == CLAIM_STATUS_FINISH) selected @endif>
                {{ Arr::get(CLAIM_STATUS_LIST, CLAIM_STATUS_FINISH) }}
            </option>
        </select>
    </div>
    <button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel"
        data-download-url="{{ route('main.withdraw.histories.download', ['fileType' => 'xlsx']) }}">
        <i class="fa-solid fa-file-excel me-1 text-info"></i>Excel
    </button>
    {{-- <button type="button" class="d-none dt-button button-html5 btn-exports" title="Download PDF"
        data-download-url="{{ route('main.withdraw.histories.download', ['fileType' => 'pdf']) }}">
        <i class="fa-solid fa-file-pdf me-1 text-danger"></i>PDF
    </button> --}}
@endsection

@php
    $myDtButtons = [
        [
            'id' => 'btn-refresh',
            'html' => '<i class="fa-solid fa-rotate"></i>',
            'title' => 'Refresh',
            'onclick' => 'refreshTable();',
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

        function formatNumber(value) {
            return $.number(value, 0, ',', '.');
        }

        function getFilterUrl() {
            return '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date')
                .val() + '&bonus_type=' + $('#bonus-type').val() + '&select_status=' + $('#select-status').val();
        }

        function setTotal() {
            const totalan = $('#total-bonusan');

            data = {
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
            };

            $.ajax({
                url: '{{ $totalUrl }}' + getFilterUrl(),
                type: 'GET',
                dataType: 'JSON',
                data: data
            }).done(function(respon) {
                totalan.empty().html(formatNumber(respon.total));
            }).fail(function(respon) {
                totalan.empty().html(0);
            });
        }

        function refreshTable() {
            table.ajax.reload();
        }

        function doExport(obj) {
            let url = obj.data('download-url') + getFilterUrl();

            window.open(url);
        }

        $(function() {
            table = $('#table').DataTable({
                dom: datatableDom,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ $dataUrl }}',
                    data: function(d) {
                        d.start_date = $('#start-date').val();
                        d.end_date = $('#end-date').val();
                        d.select_status = $('#select-status').val();
                    }
                },
                deferLoading: 50,
                info: true,
                searching: false,
                pagingType: datatablePagingType,
                lengthMenu: [10, 25, 50],
                language: datatableLanguange,
                buttons: datatableButtons,
                columns: [{
                        data: 'wd_date',
                        searchable: false
                    },
                    {
                        data: 'wd_code'
                    },
                    {
                        data: 'wd_bonus_type',
                        searchable: false
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'username',
                        visible: false
                    },
                    {
                        data: 'bank_name',
                        searchable: false
                    },
                    {
                        data: 'bank_acc_no',
                        visible: false
                    },
                    {
                        data: 'bank_acc_name',
                        visible: false
                    },
                    {
                        data: 'total_bonus',
                        className: 'dt-body-right',
                        searchable: false
                    },
                    {
                        data: 'fee',
                        className: 'dt-body-right',
                        searchable: false
                    },
                    {
                        data: 'total_transfer',
                        className: 'dt-body-right',
                        searchable: false
                    },
                    {
                        data: 'status',
                        className: 'dt-body-center',
                        searchable: false,
                    },
                ],
            }).on('draw.dt', function() {
                setTotal();
            });

            const dtWrapper = $('#table_wrapper.dataTables_wrapper');
            $('#bonus-filter').prependTo(dtWrapper);

            customizeDatatable();

            const dtControlButton = $('.datatable-controls', dtWrapper);
            const btnLast = $('.datatable-controls > :first-child > :last-child', dtWrapper);
            btnLast.addClass('me-2').after($('#table_length')).after($('#filter-status').removeClass('d-none'));

            const myRefreshBtn = $('#btn-refresh');
            const myBtnExports = $('.btn-exports');

            $.each(myBtnExports, function(k, b) {
                myRefreshBtn.before($(b).removeClass('d-none'));
            });

            myBtnExports.on('click', function(e) {
                doExport($(this));
            });

            $('.bs-date').datepicker({
                autoclose: true,
                language: 'id',
                disableTouchKeyboard: true,
                todayHighlight: true
            });

            $('.bs-date, #filter-status').on('change', function() {
                refreshTable();
            });

            refreshTable();
        });
    </script>
@endpush
