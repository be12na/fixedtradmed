@extends('layouts.app-main')

@section('content')
@include('partials.alert')

{{-- <div class="dataTables_filter me-1" id="mitra-filter">
    <select class="form-select form-select-sm mx-0" id="branch-id" autocomplete="off">
        <option value="-1" data-zone="">-- Pilih Cabang --</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @optionSelected($branch->id, $currentBranchId)>{{ $branch->name }}</option>
        @endforeach
    </select>
</div> --}}

<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th>Tgl. Daftar</th>
            <th class="border-start">Username</th>
            <th class="border-start">Nama</th>
            <th class="border-start">Email</th>
            <th class="border-start">Hp</th>
            <th class="border-start">Paket</th>
            <th class="border-start">Referral</th>
            {{-- <th class="border-start">Cabang</th> --}}
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="small"></tbody>
</table>
@endsection

@php
    $canAction = hasPermission('main.mitra.register.action');
@endphp

@push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])

@if ($canAction)
    <div class="modal fade" id="my-modal-confirm" tabindex="-1" style="z-index: 2000;">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header fw-bold small py-2 px-3">Konfirmasi</div>
                <div class="modal-body bg-light">
                    <div class="row g-2">
                        <div class="col-12">
                            <span class="" id="confirm-text"></span> calon member tersebut sebagai Member ?
                        </div>
                        <div class="col-12 d-none" id="reason-reject-container">
                            <label class="d-block required">Alasan</label>
                            <textarea id="input-reason" class="form-control" autocomplete="off"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <form method="POST" id="myConfirmForm" class="disable-submit">
                        @csrf
                        <input type="hidden" name="action_value" id="action-value">
                        <input type="hidden" name="mitra_type" id="mitra-type-value">
                        <input type="hidden" name="reject_reason" id="reject-reason">
                        <button type="button" class="btn btn-sm btn-primary" onclick="actionConfirm();">
                            <i class="fa-solid fa-thumbs-up me-1"></i>
                            OK
                        </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
                        <i class="fa-solid fa-undo me-1"></i>
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
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

@if ($canAction)
<script>
    function actionConfirm()
    {
        // $('#branch-value').val($('#branch-select').val());
        $('#mitra-type-value').val($('#mitra-type-select').val());
        $('#mitra-level-value').val($('#mitra-level-select').val());
        $('#reject-reason').val($('#input-reason').val());
        $('#my-modal-confirm').modal('hide');
        submitForm('#myConfirmForm', '#alert-detail-container');
    }
    function openConfirmModal(target)
    {
        const value = target.data('confirm-value');
        const text = target.data('confirm-text');
        const ct = $('#confirm-text');
        ct.removeClass('text-danger text-success').html(text);
        $('#action-value').val(value);

        $('#myConfirmForm').attr('action', target.data('form-action'));
        
        if (value == 'confirm') {
            ct.addClass('text-success');
            $('#reason-reject-container').addClass('d-none');
        } else if (value == 'reject') {
            ct.addClass('text-danger');
            $('#reason-reject-container').removeClass('d-none');
        }
        
        $('#my-modal-confirm').modal('show');
    }
</script>
@endif

<script>
    let table;

    function refreshTable()
    {
        table.ajax.reload();
    }

    function openDetailModal(target)
    {
        const detailModal = $('#my-modal');
        detailModal.find('#my-modal-dialog').empty().load(target.data('modal-url'));
        detailModal.modal('show');
    }

    $(function() {
        table = $('#table').DataTable({
            dom: datatableDom,
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("main.mitra.register.datatable") }}',
                data: function(d) {
                    d.branch_id = $('#branch-id').val();
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
            order: [[0, 'asc']],
            columns: [
                {data: 'tgl_register', searchable: false},
                {data: 'username'},
                {data: 'mitra_name'},
                {data: 'email'},
                {data: 'phone'},
                {data: 'package_id', searchable: false},
                {data: 'referral_name'},
                // {data: 'branch_name', searchable: false},
                {data: 'view', searchable: false, orderable: false, className: 'dt-body-center', exportable: false, printable:false},
            ],
        });
        
        customizeDatatable();

        refreshTable();
    });
</script>
@endpush
