@extends('layouts.app-main')

@php
    $hasRole = hasPermission('main.withdraw.transfer.index');
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
                @if ($hasRole)
                    <th class="d-none">
                        <input type="checkbox" class="mx-3" id="check-all" title="Pilih Semua" autocomplete="off"
                            onclick="selectCheck($(this));">
                    </th>
                @endif
                <th class="border-start">Tanggal</th>
                <th class="border-start">Kode</th>
                <th class="border-start">Member</th>
                <th></th>
                <th class="border-start">Bank</th>
                <th></th>
                <th></th>
                <th class="border-start">Total Bonus</th>
                <th class="border-start">Admin Fee</th>
                <th class="border-start">Total Transfer</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    {{-- <button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel" data-download-url="{{ route($downloadRouteName, ['fileType' => 'xlsx']) }}">
    <i class="fa-solid fa-file-excel me-1 text-primary"></i>Excel
</button>
<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download PDF" data-download-url="{{ route($downloadRouteName, ['fileType' => 'pdf']) }}">
    <i class="fa-solid fa-file-pdf me-1 text-primary"></i>PDF
</button> --}}

    @if ($hasRole)
        <div class="btn-group me-1 mb-1 d-none" id="button-action">
            <button type="button" id="btn-submit-check" class="btn btn-sm btn-primary" title="Submit"
                data-bs-toggle="modal" data-bs-target="#modal-transfer" data-modal-url="{{ $transferUrl }}">
                <i class="fa-solid fa-save me-1" style="line-height: inherit"></i>
                Submit
            </button>
            <button type="button" id="btn-cancel" class="btn btn-sm btn-warning" title="Batal"
                onclick="transferAction('hide');">
                <i class="fa-solid fa-times me-1" style="line-height: inherit"></i>
                Batal
            </button>
        </div>
        <div class="btn-group me-1 mb-1 d-none" id="button-transfer">
            <button type="button" class="btn btn-sm btn-success" title="Transfer" onclick="transferAction('show');">
                Transfer
            </button>
        </div>
        @push('includeContent')
            @include('partials.modals.modal', ['bsModalId' => 'modal-transfer', 'scrollable' => true])
        @endpush
    @endif
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

        // function doExport(obj)
        // {
        //     let url = obj.data('download-url') + '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&bonus_type=' + $('#bonus-type').val();

        //     window.open(url);
        // }

        @if ($hasRole)
            let showHide = 'hide';

            function showHideCheck() {
                const th = $('table.dataTable thead > tr > :first-child');
                const td = $('table.dataTable tbody > tr > :first-child:not([colspan])');
                const b1 = $('#button-action');
                const b2 = $('#button-transfer');
                if (showHide === 'show') {
                    th.removeClass('d-none');
                    td.removeClass('d-none');
                    b1.removeClass('d-none');
                    b2.addClass('d-none');
                } else if (showHide === 'hide') {
                    th.addClass('d-none');
                    td.addClass('d-none');
                    b1.addClass('d-none');
                    b2.removeClass('d-none');
                }
            }

            function selectCheck(ch) {
                const ca = $('#check-all');
                const tc = $('table.dataTable tbody .check-row');
                if (!ch.hasClass('check-row')) {
                    tc.prop({
                        checked: ca.is(':checked')
                    }).trigger('change');
                } else {
                    const checked = $('table.dataTable tbody .check-row:checked');
                    ca.prop({
                        checked: (checked.length == tc.length)
                    });
                }
            }

            function transferAction(am) {
                showHide = am;
                showHideCheck();
            }

            function changeDataModalUrl() {
                const checks = $('table.dataTable tbody .check-row:checked');
                let arrCheck = [];
                $.each(checks, function(k, v) {
                    arrCheck.push($(v).val());
                });

                const p = arrCheck.length ? '&ids=' + arrCheck.join(',') : '';
                const url = '{{ $transferUrl }}' + p;

                $('#btn-submit-check').data('modal-url', url);
            }
        @endif

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
                deferLoading: 50,
                info: true,
                searching: false,
                pagingType: datatablePagingType,
                lengthMenu: [10, 25, 50],
                language: datatableLanguange,
                buttons: datatableButtons,
                order: [
                    [
                        @if ($hasRole)
                            1
                        @else
                            0
                        @endif , 'asc'
                    ]
                ],
                columns: [
                    @if ($hasRole)
                        {
                            data: 'check',
                            className: 'dt-body-center',
                            orderable: false,
                            searchable: false
                        },
                    @endif {
                        data: 'wd_date',
                        searchable: false
                    },
                    {
                        data: 'wd_code'
                    },
                    {
                        data: 'name'
                    },
                    {
                        data: 'username',
                        className: 'd-none'
                    },
                    {
                        data: 'bank_name',
                        searchable: false
                    },
                    {
                        data: 'bank_acc_no',
                        className: 'd-none'
                    },
                    {
                        data: 'bank_acc_name',
                        className: 'd-none'
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
                    }
                ],
                @if ($hasRole)
                    drawCallback: function(settings) {
                        showHideCheck();
                        const ca = $('#check-all');
                        ca.prop({
                            checked: false
                        });
                        selectCheck(ca);
                    },
                @endif
            }).on('draw.dt', function() {
                setTotal();
            });

            const dtWrapper = $('#table_wrapper.dataTables_wrapper');
            $('#bonus-filter').prependTo(dtWrapper);

            customizeDatatable();

            const dtControlButton = $('.datatable-controls', dtWrapper);
            const btnLast = $('.datatable-controls > :first-child > :last-child', dtWrapper);
            btnLast.addClass('me-2').after($('#table_length'));

            const myRefreshBtn = $('#btn-refresh');

            // const myBtnExports = $('.btn-exports');

            // $.each(myBtnExports, function(k, b) {
            //     myRefreshBtn.before($(b).removeClass('d-none'));
            // });

            // myBtnExports.on('click', function(e) {
            //     doExport($(this));
            // });

            @if ($hasRole)
                myRefreshBtn.before($('#button-action')).before($('#button-transfer').removeClass('d-none'));

                $(document).on('click', 'table.dataTable tbody .check-row', function(e) {
                    selectCheck($(this));
                }).on('change', 'table.dataTable tbody .check-row', function(e) {
                    const me = $(this);
                    const tr = me.closest('tr');
                    if (me.is(':checked')) {
                        tr.addClass('selected');
                    } else {
                        tr.removeClass('selected');
                    }

                    changeDataModalUrl();
                });

                $('#check-all').on('change', function(e) {
                    changeDataModalUrl();
                });

                // $('#btn-submit-check').on('click', function(e) {
                //     const checks = $('table.dataTable tbody .check-row:checked');
                //     let arrCheck = [];
                //     $.each(checks, function(k, v) {
                //         arrCheck.push($(v).val());
                //     });

                //     const me = $(this);
                //     const p = arrCheck.length ? '&ids=' + arrCheck.join(',') : '';
                //     let url = '{{ $transferUrl }}?type={{ BONUS_MITRA_SPONSOR }}' + p;
                //     me.data('modal-url', url);
                // });
            @endif

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
