@extends('layouts.app-main')

@section('content')
    @include('partials.alert')

    @php
        $canEdit = hasPermission('main.settings.quota.edit');
    @endphp

    <table class="table table-sm table-nowrap table-striped table-hover small" id="table">
        <thead class="bg-gradient-brighten bg-white">
            <tr class="text-center">
                <th>Paket</th>
                <th class="border-start">Quota Belanja (Rp)</th>
                <th class="border-start">Poin Belanja</th>
                @if ($canEdit)
                    <th class="border-start"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->name }}</td>
                    <td class="text-center">@formatNumber($row->quota)</td>
                    <td class="text-center">@formatNumber($row->point)</td>
                    @if ($canEdit)
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-success btn-data-control"
                                data-modal-url="{{ route('main.settings.quota.edit', ['quotaPackage' => $row->package_id]) }}"
                                title="Edit" data-bs-toggle="modal" data-bs-target="#my-modal">
                                <i class="fa-solid fa-pencil-alt"></i>
                            </button>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
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

@push('scripts')
    @include('partials.datatable-custom', [
        'datatableResponsive' => 'sm',
    ])

    <script>
        $(function() {
            $('#table').DataTable({
                dom: datatableDom,
                info: false,
                lengthChange: false,
                searching: false,
                paging: false,
                sort: false,
                language: datatableLanguange,
                buttons: datatableButtons,
            });

            customizeDatatable();
        });
    </script>
@endpush
