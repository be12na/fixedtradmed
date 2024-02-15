@extends('layouts.app-member')

@section('bodyClass', 'select2-40')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 fs-auto border-bottom pb-2" id="sale-filter">
    <div class="row g-2">
        @php
            $endDate = formatFullDate(\Carbon\Carbon::today());
        @endphp
        <div class="col-sm-6 col-md-3">
            <div>Dari Tanggal</div>
            <div class="input-group">
                <input type="text" id="start-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['start'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                <label for="start-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div>Sampai Tanggal</div>
            <div class="input-group">
                <input type="text" id="end-date" class="form-control bg-white bs-date" value="@formatFullDate($dateRange['end'])" data-date-format="d MM yyyy" data-date-end-date="{{ $endDate }}" placeholder="Tanggal" autocomplete="off" readonly="">
                <label for="end-date" class="input-group-text cursor-pointer">
                    <i class="fa-solid fa-calendar-days"></i>
                </label>
            </div>
        </div>
        @if ($isManager)
            <div class="col-sm-6 col-md-3">
                <div>Cabang</div>
                <select class="form-select" id="branch-id" autocomplete="off">
                    <option value="-1" data-zone="">-- Pilih Cabang --</option>
                    @foreach ($branches as $branch)
                    {{-- data-zone="{{ strtolower(Arr::get(BRANCH_ZONES, $branch->wilayah, '')) }}" --}}
                        <option value="{{ $branch->id }}" @optionSelected($branch->id, $currentBranchId)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <div>Salesman</div>
                <select class="form-select select2bs4 select2-custom" id="salesman-id">
                    @if (!empty($currentSalesman))
                        <option value="{{ $currentSalesman->id }}">
                            {{ $currentSalesman->name }} ({{ $currentSalesman->internal_position_code }})
                        </option>
                    @endif
                </select>
            </div>
        @else
            <input type="hidden" id="branch-id" value="{{ Auth::user()->branch_id }}">
            <input type="hidden" id="salesman-id" value="{{ Auth::user()->id }}">
        @endif
    </div>
</div>
<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Kode</th>
            <th class="border-start">Sales</th>
            <th class="border-start">Manager</th>
            <th class="border-start">Cabang</th>
            <th class="border-start">Total</th>
            {{-- <th class="border-start">Savings</th> --}}
            <th class="border-start">Catatan</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
    <tfoot class="small fw-bold border-top-gray-500">
        <tr>
            <td class="text-end" colspan="5">Total</td>
            <td class="text-end"></td>
            {{-- <td class="text-end"></td> --}}
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>

<div class="row gx-2 gx-md-3 justify-content-md-around order-first order-md-5 fw-bold mb-2 me-0 me-md-auto flex-md-fill" id="content-summary">
    <div class="col-12 col-md-auto d-flex align-items-center">
        <div class="row gx-1 flex-nowrap flex-fill">
            <div class="col-4 col-sm-3 col-md-auto">Total</div>
            <div class="col-auto">:</div>
            <div class="col-auto" id="total-profit-all"></div>
        </div>
    </div>
    {{-- <div class="col-12 col-md-auto d-flex align-items-center">
        <div class="row gx-1 flex-nowrap flex-fill">
            <div class="col-4 col-sm-3 col-md-auto">Savings</div>
            <div class="col-auto">:</div>
            <div class="col-auto" id="total-savings-all"></div>
        </div>
    </div> --}}
</div>

@endsection

@push('includeContent')
    @include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
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
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
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
    $canCreate = canSale();
    $myDtButtons = [];
    if ($canCreate) {
        $createUrl = route('member.sale.create');
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

    $hasMyButtons = (count($myDtButtons) > 0);
@endphp

@push('scripts')

@include('partials.datatable-custom', [
    'datatableButtons' => $myDtButtons,
    'datatableResponsive' => 'md',
])

{{-- 
{data: 'savings', searchable: false, className: 'dt-body-right'},
// savings
let totalSavings = api.column( 6, { page: 'current'} ).data().reduce( function (a, b) {
    return parseInt(a) + parseInt(b);
}, 0 );
$('td:eq(6)', row).html(formatCurrency(data.savings));
$( api.column( 6 ).footer() ).html(formatCurrency(totalSavings));
// saving all
let totalSavingAll = api.column( 6 ).data().reduce( function (a, b) {
    return parseInt(a) + parseInt(b);
}, 0 );
$('#total-savings-all').html(formatCurrency(totalSavingAll));
--}}

<script>
    let table, select_branch, select_salesman;

    function formatCurrency(value)
    {
        return '<sup class="me-1 fw-normal">Rp</sup>' + $.number(value, 0, ',', '.');
    }
    function refreshTable()
    {
        table.ajax.reload();
    }

    $(function() {
        select_branch = $('#branch-id');
        select_salesman = $('#salesman-id');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("member.sale.datatable") }}',
                data: function(d) {
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
                    d.branch_id = select_branch.val();
                    d.salesman_id = select_salesman.val();
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
                {data: 'salesman_name'},
                {data: 'manager_name'},
                {data: 'branch_name'},
                {data: 'stotal_price', searchable: false, className: 'dt-body-right'},
                {data: 'salesman_note'},
                {data: 'view', searchable: false, orderable: false, className: 'dt-body-center'},
            ],
            rowCallback: function(row, data) {
                $('td:eq(5)', row).html(formatCurrency(data.stotal_price));
            },
            footerCallback: function(row, data, start, end, display) {
                const api = this.api();
                // omzet
                let totalOmzet = api.column( 5, { page: 'current'} ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $( api.column( 5 ).footer() ).html(formatCurrency(totalOmzet));
                // profit all
                let totalOmzetAll = api.column( 5 ).data().reduce( function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0 );
                $('#total-profit-all').html(formatCurrency(totalOmzetAll));
            }
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');

        $('#sale-filter').prependTo(dtWrapper);
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

        refreshTable();
    });
</script>

@if ($isManager)
<script>
    $(function() {
        select_salesman.select2({
            theme: 'classic',
            placeholder: '-- Salesman --',
            allowClear: true,
            ajax: {
                url: function(params) {
                    params.current = select_salesman.val();
                    params.branch = select_branch.val();
                    
                    return '{{ route("member.sale.crew") }}';
                },
                data: function (params) {
                    let dt = {
                        search: params.term,
                        current: params.current,
                        branch: params.branch
                    };

                    return dt;
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
            },
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: function(data) {
                return data.html;
            },
            templateSelection: function(data) {
                return data.text;
            }
        }).on('change', function (e) {
            refreshTable();
        });

        $('#branch-id').on('change', function(e) {
            select_salesman.empty();
            refreshTable();
        });
    });
</script>
@endif
@endpush
