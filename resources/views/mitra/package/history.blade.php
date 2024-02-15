@extends('layouts.app-mitra')

@section('content')
@include('partials.alert')

<table class="table table-sm table-nowrap table-striped table-hover small" id="table">
    <thead class="bg-gradient-brighten bg-white">
        <tr class="text-center">
            <th>Invoice</th>
            <th class="border-start">Tanggal</th>
            <th class="border-start">Jenis</th>
            <th class="border-start">Paket</th>
            <th class="border-start">Transfer</th>
            <th class="border-start">Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($userPackages as $userPackage)
            <tr>
                <td>
                    <a class="text-decoration-none" href="{{ $userPackage->repeat_order ? route('mitra.package.ro.transfer', ['userPackageRO' => $userPackage->id]) : route('mitra.package.index') }}">
                        {{ $userPackage->code }}
                    </a>
                </td>
                <td>@formatDatetime($userPackage->created_at, 'j M Y, H:i')</td>
                <td>{{ $userPackage->type_name }}</td>
                <td>{{ $userPackage->package_name }}</td>
                <td>{{ $userPackage->total_price }}</td>
                <td class="text-center">
                    @php
                        $badgeCls = 'bg-secondary text-light';
                        if ($userPackage->is_transferred) {
                            $badgeCls = 'bg-info text-dark';
                        } elseif ($userPackage->is_rejected) {
                            $badgeCls = 'bg-warning text-dark';
                        } elseif ($userPackage->is_confirmed) {
                            $badgeCls = 'bg-success text-light';
                        }
                    @endphp
                    <span class="badge rounded-pill {{ $badgeCls }}">
                        {{ $userPackage->is_transferred ? 'Konfirmasi' : ($userPackage->is_rejected ? 'Ditolak' : ($userPackage->is_confirmed ? 'Tuntas' : 'Transfer')) }}
                    </span>
                </td>
            </tr>            
        @endforeach
    </tbody>
</table>
@endsection

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
    'datatableButtons' => [],
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
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[0, 'desc']],
        });

        customizeDatatable();
    });
</script>
@endpush
