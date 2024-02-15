@extends('layouts.app-main')

@section('content')
@include('partials.alert')

@php
$filterCheck = '';
if (isset($filterIntExt) && ($filterIntExt === true)) {
    $filterCheck = '<div class="col-12"><div class="form-check"><input type="checkbox" class="form-check-input" id="internal-check" value="1" autocomplete="off" checked><label for="internal-check" class="form-check-label">Internal</label></div></div>';
}
@endphp

<input type="hidden" id="bonus-type" value="{{ $bonusType }}">
<div class="row g-2 mb-2" id="bonus-filter">
    <div class="col-md-5 col-lg-4 d-flex">
        <div class="d-flex flex-column p-2 p-md-3 w-100 border rounded-3 bg-gray-100 bg-gradient">
            <div class="d-block flex-shrink-0 text-center fw-bold mb-3">Total</div>
            <div class="flex-fill d-flex flex-nowrap align-items-center justify-content-center fs-2 fw-bold" id="total-bonusan"></div>
        </div>
    </div>
    <div class="col-md-7 col-lg-8 d-flex">
        @include('main.bonuses.menu.date-filter', [
            'otherContent' => $filterCheck
        ])
    </div>
</div>

<table class="table table-sm table-nowrap table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Anggota</th>
            <th class="border-start">Posisi</th>
            <th class="border-start">Cabang / No. Transaksi</th>
            <th class="border-start">Omzet</th>
            <th class="border-start">Bonus</th>
            <th class="border-start">Jml. Bonus</th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>

<button type="button" class="d-none dt-button button-html5 btn-exports" title="Download Excel" data-download-url="{{ $downloadExcel }}">
    <i class="fa-solid fa-file-excel me-1 text-primary"></i>Excel
</button>
{{-- <button type="button" class="dt-button button-html5 btn-exports">
    <i class="fa-solid fa-file-pdf me-1 text-primary"></i>PDF
</button> --}}
@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('styles')
<style>
    #table > thead > tr > :nth-child(1) {
        width: 60px !important;
    }
    #table > thead > tr > :nth-child(4) {
        width: 160px !important;
    }
    #table > tbody > tr.group > * {
        font-weight: 600;
        box-shadow: none !important;
    }
    #table > tbody > tr.group.group-1 > * {
        background-color: var(--bs-primary) !important;
        color: var(--bs-light);
    }
    #table > tbody > tr.group.group-2 > * {
        background-color: var(--bs-gray-300) !important;
    }
    .table {
        width: 100% !important;
    }
</style>
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

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }

    function renderSummaryUser(data)
    {
        const target = $(data.target_id);
        if (target.length > 0) {
            let row = target.clone();
            let detail = target;
            row.removeClass('group group-2').children('td:nth-child(2), td:nth-child(3)').html('');
            $.each(data.details, function(k, v) {
                row.children('td:nth-child(4)').html(v.branch);
                row.children('td:nth-child(5)').html(v.omzet);
                row.children('td:nth-child(6)').html(v.percent);
                row.children('td:nth-child(7)').html(v.bonus);

                detail.after(row);
                detail = row;
                row = row.clone();
            });
        }
    }

    function setTotal()
    {
        const totalan = $('#total-bonusan');

        data = {
            start_date: $('#start-date').val(),
            end_date: $('#end-date').val(),
            bonus_type: $('#bonus-type').val(),
        };

        if (filterCheck.length > 0) {
            data.internal = filterCheck.is(':checked') ? 1 : 0;
        }

        $.ajax({
            url: '{{ $totalUrl }}',
            type: 'GET',
            dataType: 'JSON',
            data: data
        }).done(function(respon) {
            totalan.empty().html(respon.total_all);
            $.each(respon.rows, function(k, v) {
                renderSummaryUser(v);
            });
        }).fail(function(respon) {
            totalan.empty().html(0);
        });
    }

    function refreshTable()
    {
        table.ajax.reload();
    }

    $(function() {
        const groupColumn = 1;
        filterCheck = $('#internal-check');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ $dataUrl }}',
                data: function(d) {
                    const order = d.order[0];

                    if (order.column == 1) {
                        d.order = [
                            {column: 0, dir: 'asc'},
                            {column: 2, dir: 'asc'},
                            order
                        ];
                    } else if (order.column == 0) {
                        d.order = [
                            order,
                            {column: 2, dir: 'asc'},
                            {column: 1, dir: 'asc'}
                        ]
                    }

                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.bonus_type = $('#bonus-type').val();

                    if (filterCheck.length > 0) {
                        d.internal = filterCheck.is(':checked') ? 1 : 0;
                    }
                }
            },
            deferLoading: 50,
            info: true,
            search: {
                return: true
            },
            pagingType: datatablePagingType,
            lengthMenu: [10, 25, 50],
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'desc']],
            columnDefs: [
                {orderable: false, targets: [3, 4, 5, 6]},
                {searchable: false, targets: [0, 2, 3, 4, 5, 6]}
            ],
            columns: [
                {data: 'tanggal'},
                {data: 'name'},
                {data: 'position_id'},
                {data: 'branch_names'},
                {data: 'omzets_transaction', className: 'dt-body-right'},
                {data: 'percents_bonus', className: 'dt-body-center'},
                {data: 'total_user', className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
            ],
            drawCallback: function (settings) {
                const api = this.api();
                const apiRows = api.rows({ page: 'current' });
                const rows = apiRows.nodes();
                let g1 = null;
                let g2 = null;
                let total1 = {};

                apiRows.every(function ( rowIdx, tableLoop, rowLoop ) {
                    const data = this.data();

                    // group tanggal
                    if (data.bonus_date != g1) {
                        $(rows).eq(rowIdx).before(
                            '<tr class="group group-1">' + 
                                '<td colspan="6">' + data.tanggal + '</td>' + 
                                '<td class="text-end fw-bold">' + formatCurrency(data.total_tgl) + '</td>' + 
                            '</tr>'
                        );
                        
                        g1 = data.bonus_date;
                    }

                    // group user
                    const rowGroupUserId = 'groupUser___' + data.bonus_date + '___' + data.user_id;
                    
                    $(rows).eq(rowIdx)
                        .addClass('group group-2')
                        .attr({id: rowGroupUserId})
                        .children('td:nth-child(1), td:nth-child(4), td:nth-child(5), td:nth-child(6)')
                        .html('');
                });
            },
        }).on( 'draw.dt', function () {
            setTotal();
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');
        $('#bonus-filter').prependTo(dtWrapper);

        customizeDatatable();

        const dtControlButton = $('.datatable-controls', dtWrapper);
        const btnLast = $('.datatable-controls > :first-child > :last-child', dtWrapper);
        btnLast.addClass('me-2').after($('#table_length'));

        const btnFirst = $('.datatable-controls > :first-child > :first-child', dtWrapper);
        btnFirst.addClass('me-2').find('.dt-buttons').append($('.btn-exports').removeClass('d-none'));

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

        $('.btn-exports').on('click', function(e) {
            let url = $(this).data('download-url') + '?start_date=' + $('#start-date').val() + '&end_date=' + $('#end-date').val() + '&bonus_type=' + $('#bonus-type').val();

            if (filterCheck.length > 0) {
                const cek = filterCheck.is(':checked') ? 1 : 0;
                url = url + '&internal=' + cek;
            }

            window.open(url);
        });
    });
</script>

@endpush