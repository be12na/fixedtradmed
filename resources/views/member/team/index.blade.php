@extends('layouts.app-member')

@section('content')
<div class="d-block">
    @include('partials.alert')
    <table class="table table-sm table-nowrap table-striped table-hover" id="table">
        <thead class="bg-gradient-brighten bg-white small">
            <tr class="text-center">
                <th><i class="fa-solid fa-image"></i></th>
                <th class="border-start">Nama</th>
                <th class="border-start">Username</th>
                <th class="border-start">Email</th>
                <th class="border-start">No. HP</th>
                <th class="border-start">Posisi</th>
                <th class="border-start">Upline</th>
                <th class="border-start">Cabang</th>
                <th class="border-start">Status</th>
            </tr>
        </thead>
        <tbody class="small">
            @php $noUrut = 1; @endphp
            @foreach ($activeTeams as $row)
                <tr>
                    <td>
                        <div class="d-flex justify-content-center">
                            <div class="d-block text-center align-middle border rounded-circle overflow-hidden bg-dark" style="width:50px; height:50px; --bs-bg-opacity:0.1;">
                                <img alt="" src="" style="width:auto; height:100;">
                            </div>
                        </div>
                    </td>
                    <td>{{ $row->name }}</td>
                    <td>{{ $row->username }}</td>
                    <td>{{ $row->email }}</td>
                    <td>{{ $row->phone }}</td>
                    <td>
                        <div>{{ $row->internal_position_name  }}</div>
                    </td>
                    <td>{{ $row->upline->name }}</td>
                    <td>
                        @if ($row->activeBranches->isNotEmpty())
                            <ul class="ps-3">
                                @foreach ($row->activeBranches as $rowBranch)
                                    <li>{{ $rowBranch->branch->name }}</li>
                                @endforeach
                            </ul>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @contentCheck($row->user_status == USER_STATUS_ACTIVE)
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
</div>
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
        $(function() {
            $('#table').DataTable({
                dom: datatableDom,
                info: false,
                lengthChange: false,
                pagingType: datatablePagingType,
                lengthMenu: datatableLengMenu,
                language: datatableLanguange,
                buttons: datatableButtons,
                order: [[1, 'asc']],
                columnDefs: [
                    {orderable: false, targets: [0, 8]}
                ]
            });
    
            customizeDatatable();
        });
    });
</script>
@endpush
