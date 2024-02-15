@extends('layouts.app-member')

@section('bodyClass', 'select2-40')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 fs-auto border-bottom pb-2" id="payment-filter">
    <div class="row g-2">
        @php
            $endDate = formatFullDate(\Carbon\Carbon::today());
        @endphp
        <div class="col-lg-5">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div>Dari Tanggal</div>
                    <div class="input-group">
                        <input type="text" id="start-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['start'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                        <label for="start-date" class="input-group-text cursor-pointer">
                            <i class="fa-solid fa-calendar-days"></i>
                        </label>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div>Sampai Tanggal</div>
                    <div class="input-group">
                        <input type="text" id="end-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['end'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                        <label for="end-date" class="input-group-text cursor-pointer">
                            <i class="fa-solid fa-calendar-days"></i>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="row g-2">
                <div class="col-sm-4">
                    <div>Cabang</div>
                    <select class="form-select" id="branch-id" autocomplete="off">
                        <option value="-1" data-zone="">-- Pilih Cabang --</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @optionSelected($branch->id, $currentBranchId)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-5">
                    <div>Bank</div>
                    <select class="form-select" id="bank-code">
                        <option value="">-- Bank --</option>
                        @foreach (BANK_TRANSFER_LIST as $value => $text)
                            <option value="{{ $value }}" @optionSelected($value, $currentBankCode)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-3">
                    <div>Status</div>
                    <select class="form-select" id="status-id">
                        <option value="-1">-- Status --</option>
                        @foreach (PAYMENT_STATUS_LIST as $value => $text)
                            <option value="{{ $value }}" @optionSelected($value, $currentStatusId)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Kode</th>
            <th class="border-start">Manager</th>
            <th class="border-start">Cabang</th>
            <th class="border-start">Harga</th>
            <th class="border-start">Diskon</th>
            <th class="border-start">Transfer</th>
            <th class="border-start">Status</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
    <tfoot class="small fw-bold border-top-gray-500">
        <tr>
            <td class="text-end" colspan="4">Total</td>
            <td class="text-end"></td>
            <td class="text-end"></td>
            <td class="text-end"></td>
            <td class="text-end"></td>
            <td></td>
        </tr>
    </tfoot>
</table>

<div class="row gx-2 gx-md-3 justify-content-md-around order-first order-md-5 fw-bold mb-2 me-0 me-md-auto flex-md-fill" id="content-summary">
    <div class="col-12 col-md-auto d-flex align-items-center">
        <div class="row gx-1 flex-nowrap flex-fill">
            <div class="col-4 col-sm-3 col-md-auto">Total</div>
            <div class="col-auto">:</div>
            <div class="col-auto" id="total-transfer"></div>
        </div>
    </div>
</div>
@endsection

@push('includeContent')
    @include('partials.modals.modal-lg', ['bsModalId' => 'my-modal', 'scrollable' => true])
@endpush

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker-1.9.0/css/bootstrap-datepicker.standalone.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap-datepicker-1.9.0/locales/bootstrap-datepicker.id.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('vendor/jquery/jquery.number.min.js') }}"></script>
@endpush

@push('styles')
<style>
    .table.table-detail > tbody > tr > td,
    .table.table-detail > tfoot > tr > td {
        border: none;
    }
</style>
@endpush

@php
    $canCreate = hasPermission('member.payment.create');
    $myDtButtons = [];
    if ($canCreate) {
        $createUrl = route('member.payment.create');
        $myDtButtons[] = [
            'id' => 'btn-tambah',
            'html' => 'Tambah',
            'onclick' => "window.location.href='{$createUrl}';"
        ];
    }

    $myDtButtons[] = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => "refreshTable();"
    ];
@endphp

@push('scripts')
@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'md',
])

<script>
    let table;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
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
                url: '{{ route("member.payment.datatable") }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.branch_id = $('#branch-id').val();
                    d.bank_code = $('#bank-code').val();
                    d.status_id = $('#status-id').val();
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
            order: [[0, 'desc']],
            columns: [
                {data: 'tanggal', searchable: false},
                {data: 'kode'},
                {data: 'manager_name'},
                {data: 'branch_name'},
                {data: 'total_price', searchable: false, className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
                {data: 'total_discount', searchable: false, className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
                {data: 'total_transfer', searchable: false, className: 'dt-body-right', render: function(data, type, row) {
                    return formatCurrency(data);
                }},
                {data: 'transfer_status', searchable: false, orderable: false, className: 'dt-body-center'},
                {data: 'view', searchable: false, orderable: false, className: 'dt-body-center'},
            ],
            footerCallback: function(row, data, start, end, display) {
                const api = this.api();
                // price
                let totalPrice = api.column( 4, { page: 'current'} ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $( api.column( 4 ).footer() ).html(formatCurrency(totalPrice));
                // discount
                let totalDiscount = api.column( 5, { page: 'current'} ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $( api.column( 5 ).footer() ).html(formatCurrency(totalDiscount));
                // transfer
                let totalTransfer = api.column( 6, { page: 'current'} ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $( api.column( 6 ).footer() ).html(formatCurrency(totalTransfer));
                $('#total-transfer').html(formatCurrency(totalTransfer));
            }
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');

        $('#payment-filter').prependTo(dtWrapper);
        customizeDatatable();
        
        const dtControlButton = $('.datatable-controls', dtWrapper);
        const dtLastControl = $('.datatable-controls > :last-child', dtWrapper);
        dtLastControl.addClass('order-last').removeClass('flex-fill');
        dtControlButton.prepend($('#content-summary'));

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        }).on('change', function() {
            refreshTable();
        });

        $('.bs-date, #branch-id, #status-id, #bank-code').on('change', function() {
            refreshTable();
        });

        refreshTable();
    });
</script>
@endpush