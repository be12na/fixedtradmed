@extends('layouts.app-mitra')

@section('content')
    @include('partials.alert')
    <div class="row g-2 mb-2" id="bonus-filter">
        <div class="col-md-6 col-lg-3 d-flex">
            <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
                <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total Point</div>
                <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1">
                    @formatNumber($totalPoint)
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 d-flex">
            <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
                <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total Bonus</div>
                <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold lh-1"
                    id="total-bonusan">0</div>
            </div>
        </div>
        <div class="col-12 col-lg-6 d-flex">
            @include('main.bonuses.menu.date-filter')
        </div>
    </div>
    <table class="table table-sm table-striped table-nowrap table-hover fs-auto" id="table">
        <thead class="bg-gradient-brighten bg-white">
            <tr class="text-center">
                <th>Tanggal</th>
                <th class="border-start">Bonus</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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
        let table;

        function formatNumber(value) {
            return $.number(value, 0, ',', '.');
        }

        function setTotal() {
            const totalan = $('#total-bonusan');

            data = {
                start_date: $('#start-date').val(),
                end_date: $('#end-date').val(),
            };

            $.ajax({
                url: '{{ $totalUrl }}',
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
                    }
                },
                deferLoading: 0,
                info: true,
                searching: false,
                pagingType: datatablePagingType,
                lengthMenu: [10, 25, 50],
                language: datatableLanguange,
                buttons: datatableButtons,
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'bonus_date'
                    },
                    {
                        data: 'bonus_amount',
                        searchable: false,
                        orderable: false,
                        className: 'dt-body-right',
                        render: function(data, type, row) {
                            return formatNumber(data);
                        }
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
            btnLast.addClass('me-2').after($('#table_length'));

            $('.bs-date').datepicker({
                autoclose: true,
                language: 'id',
                disableTouchKeyboard: true,
                todayHighlight: true
            });

            $('.bs-date, #internal-check').on('change', function() {
                refreshTable();
            });

            refreshTable();
        });
    </script>
@endpush
