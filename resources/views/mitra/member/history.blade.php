@extends('layouts.app-mitra')

@section('bodyClass', 'select2-40')

@section('content')
@include('partials.alert')

<div class="d-block mb-2 fs-auto border-bottom pb-2" id="history-filter">
    <div class="row g-2 mb-2">
        <div class="col-sm-6 d-flex">
            <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-primary rounded-3 bg-primary bg-gradient text-light text-nowrap">
                <div class="fs-3"><i class="fa fa-cart-shopping"></i></div>
                <div class="text-end">
                    <div class="fs-auto text-decoration-underline">Total Belanja</div>
                    <div class="fs-4 bonus-value" data-dashboard="s=direct&t=total-purchase"></div>
                </div>
            </div>
        </div>
        {{-- <div class="col-sm-6 d-flex">
            <div class="d-flex d-flex flex-wrap align-items-center justify-content-between w-100 px-3 py-2 border border-success rounded-3 bg-success bg-gradient text-light text-nowrap">
                <div class="fs-3"><i class="fa fa-coins"></i></div>
                <div class="text-end">
                    <div class="fs-auto text-decoration-underline">Bonus Upline</div>
                    <div class="fs-4 bonus-value" data-dashboard="s=bonus&t=upline"></div>
                </div>
            </div>
        </div> --}}
    </div>
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
        <div class="col-md-6">
            <div>Member</div>
            <select class="form-select select2bs4 select2-custom" id="member-id">
                @if (!empty($currentMember))
                    <option value="{{ $currentMember->id }}" selected>{{ $currentMember->name }}</option>
                @endif
            </select>
        </div>
    </div>
</div>

<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tanggal</th>
            <th class="border-start">Member</th>
            <th class="border-start">No. Belanja</th>
            <th class="border-start">Total Belanja</th>
            {{-- <th class="border-start">Bonus Upline</th> --}}
            <th class="border-start">Status</th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>
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
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>
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
])
<script>
    let table, selectMember, reloadTotal;

    function refreshTable()
    {
        table.ajax.reload();
    }

    function loadTotal()
    {
        if (reloadTotal && reloadTotal === true) {
            $('.bonus-value').each(function(e) {
                const me = $(this);
                const url = '{{ route("dashboard") }}?' + me.data('dashboard');
    
                me.html('0');
    
                $.get({
                    url: url
                }).done(function(respon) {
                    me.html(respon);
                }).fail(function(respon) {
                    me.html('<span class="text-danger">0</span>');
                });
            });
        }
    }

    $(function() {
        selectMember = $('#member-id');

        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("mitra.myMember.histories.datatable") }}',
                data: function(d) {
                    d.member_id = selectMember.val();
                    d.start_date = $('#start-date').val();
                    d.end_date = $('#end-date').val();
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
            columns: [
                {data: 'purchase_date'},
                {data: 'mitra_name'},
                {data: 'code', className: 'dt-body-center'},
                {data: 'total_transfer', className: 'dt-body-right'},
                // {data: 'bonus_referral', className: 'dt-body-right'},
                {data: 'status', searchable: false, orderable: false, className: 'dt-body-center'},
            ],
            drawCallback: function( settings ) {
                loadTotal();
            }
        });

        const dtWrapper = $('#table_wrapper.dataTables_wrapper');

        $('#history-filter').prependTo(dtWrapper);
        customizeDatatable();

        selectMember.select2({
            theme: 'classic',
            placeholder: '-- Pilih Member --',
            allowClear: true,
            ajax: {
                url: function(params) {
                    params.current = selectMember.val();
                    
                    return '{{ route("mitra.myMember.select2") }}';
                },
                data: function (params) {
                    let dt = {
                        search: params.term,
                        current: params.current,
                    };

                    return dt;
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
            },
        }).on('change', function (e) {
            refreshTable();
        });

        $('.bs-date').datepicker({
            autoclose: true,
            language: 'id',
            disableTouchKeyboard: true,
            todayHighlight: true
        }).on('change', function() {
            refreshTable();
        });
        
        reloadTotal = true;
        refreshTable();
    });
</script>
@endpush
