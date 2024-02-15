@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.master.product.create');
    $canEdit = hasPermission('main.master.product.edit');
    $canAction = $canEdit;
@endphp

@section('content')
<div class="d-block">
    <div class="d-flex flex-nowrap justify-content-between pb-2 mb-3 border-bottom">
        <div class="d-flex flex-fill align-items-center fs-auto">
            <div id="category-text"></div>
        </div>
        <div class="flex-grow-0 dropdown ms-2">
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="dropdown" data-bs-target="#select-category">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="dropdown-menu dropdown-scrollable pt-0 pb-1" id="select-category" style="--dd-max-height:calc(100vh - calc(var(--navbar-height, 80px) * 3) - 10px);">
                <div class="dropdown-header rounded-top">Pilih Kategori</div>
                <div class="dropdown-items">
                    <a href="javascript:;" class="dropdown-item item-category {{ ($currentCategoryId == -1) ? 'active' : '' }}" data-category-id="-1">
                        Semua Kategori
                    </a>
                    @foreach ($categories as $row)
                        @php
                            $selectActive = ($row->id == $currentCategoryId) ? 'active' : '';
                        @endphp
                        <a href="javascript:;" class="dropdown-item item-category {{ $selectActive }}" data-category-id="{{ $row->id }}">
                            <span class="me-1 fw-bold text-nowrap">{{ $row->merek }}</span><span>- {{ $row->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @include('partials.alert')
    <div class="row justify-content-start justify-content-sm-center" style="--bs-gutter-y:1rem; --bs-gutter-x:1rem;" id="products-container"></div>
</div>
@endsection

@if ($canCreate || $canEdit)

    @push('includeContent')
    @include('partials.modals.modal-xl', ['bsModalId' => 'my-modal', 'scrollable' => true])
    @endpush

    @push('vendorCSS')
    <link rel="stylesheet" href="{{ asset('vendor/simple-editor/css/simditor.css') }}">
    @endpush

    @push('vendorJS')
    <script src="{{ asset('vendor/simple-editor/js/module.js') }}"></script>
    <script src="{{ asset('vendor/simple-editor/js/hotkeys.js') }}"></script>
    <script src="{{ asset('vendor/simple-editor/js/simditor.js') }}"></script>
    @endpush

@endif

@push('scripts')
<script>
    function selectCategory(obj)
    {
        if (!obj.hasClass('active')) {
            showMainProcessing()
            const url = "{{ route('main.master.product.byCategory') }}/" + obj.data('category-id');
            $('#category-text').html(obj.html());
            $('.dropdown-item.item-category').removeClass('active');
            obj.addClass('active');
            $('#products-container').empty().load(url);
            stopMainProcessing();
        }
    }

    $(function() {
        $('.dropdown-item.item-category').on('click', function() {
            selectCategory($(this));
        });

        const scp = $('.dropdown-item.item-category.active');
        scp.removeClass('active').trigger('click');
    });
</script>

@if ($canCreate || $canEdit)
    @if ($isAppV2)
    <script>
        function toggleUnit(v)
        {
            const targetUnit = $('#select-for-box');
            const retailInput = $('.retail-input');
            if (v == {{ PRODUCT_UNIT_PCS }}) {
                targetUnit.addClass('d-none');
                retailInput.attr({'disabled': true});
            } else {
                targetUnit.removeClass('d-none');
                retailInput.attr({'disabled': false});
            } 
        }
    </script>
    @else
    <script>
        function toggleUnit(v)
        {
            const targetUnit = $('#select-for-box');
            const eceranBox = $('#eceran-box');
            if (v == 1) {
                targetUnit.addClass('d-none');
                eceranBox.addClass('d-none');
            } else {
                targetUnit.removeClass('d-none');
                eceranBox.removeClass('d-none');
            }
        }
    </script>
    @endif
    <script>
        function previewImage(obj, target)
        {
            const [file] = obj.files;
            if (file) {
                $(target).attr('src', URL.createObjectURL(file));
            }
        }
        function toggleUnitVolume()
        {
            const selectUnit = $('#my-modal #satuan');
            const v = selectUnit.val();
            toggleUnit(v)
        }
        $(function() {
            $(document).on('change', '#my-modal #satuan', function() {
                toggleUnitVolume();
            });
        });
    </script>
@endif
@endpush
