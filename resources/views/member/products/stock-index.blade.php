@extends('layouts.app-member')

@php
    $weekDate = datesOfWeek();
    $dropDownBranch = ($branches->count() > 1);
@endphp

@section('content')
<div class="d-block">
    <div class="d-flex flex-nowrap justify-content-between pb-2 mb-3 border-bottom">
        <div class="d-flex flex-fill align-items-center fs-auto">
            @if ($dropDownBranch)
                <div id="branch-text"></div>
            @else
                Cabang: {{ optional($branches->first())->name }}
            @endif
        </div>
        @if ($dropDownBranch)
            <div class="flex-grow-0 dropdown ms-2">
                <button type="button" class="btn btn-sm btn-success px-1 py-0" data-bs-toggle="dropdown" data-bs-target="#select-category" style="font-size:20px;">
                    <i class="bi bi-justify"></i>
                </button>
                <div class="dropdown-menu dropdown-scrollable pt-0 pb-1" id="select-category" style="--dd-max-height:calc(100vh - calc(var(--navbar-height, 80px) * 3) - 10px);">
                    <div class="dropdown-header rounded-top">Pilih Kantor Cabang</div>
                    <div class="dropdown-items">
                        @foreach ($branches as $row)
                            @php
                                $selectActive = ($row->id == $currentBranchId) ? 'active' : '';
                            @endphp
                            <a href="javascript:;" class="dropdown-item item-branch {{ $selectActive }}" data-branch-id="{{ $row->id }}">
                                <span class="text-nowrap">{{ $row->name }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    @include('partials.alert')
    <div class="d-block w-100" id="products-container"></div>
</div>
@endsection

@push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal'])
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
    $btnRefresh = [
        'id' => 'btn-refresh',
        'html' => '<i class="fa-solid fa-rotate"></i>',
        'title' => 'Refresh',
        'onclick' => $dropDownBranch ? "$('.dropdown-item.item-branch.active').click();" : "openProducts('{$currentBranchId}');"
    ];
@endphp

@include('partials.datatable-custom', [
    'datatableButtons' => [$btnRefresh],
    'datatableResponsive' => 'sm',
])

<script>
    function openProducts(branchId)
    {
        showMainProcessing();
        const url = "{{ route('member.product.stock.index') }}/product-list/" + branchId;
        $('#products-container').empty().load(url);
        stopMainProcessing();
    }
</script>

@if ($dropDownBranch)
    <script>
    function selectBranch(obj)
    {
        if (!obj.hasClass('active')) {
            openProducts(obj.data('branch-id'));
            $('#branch-text').html('Cabang: ' + obj.html());
            $('.dropdown-item.item-branch').removeClass('active');
            obj.addClass('active');
        }
    }
    $(function() {
        $('.dropdown-item.item-branch').on('click', function() {
            const me = $(this);
            if (me.hasClass('active')) {
                me.removeClass('active');
            }
            selectBranch(me);
        });

        $('.dropdown-item.item-branch.active').click();
    });
    </script>
@else
    @if ($branches->count() > 0)
    <script>
        openProducts('{{ $branches->first()->id }}');
    </script>
    @endif
@endif
@endpush

