@extends('layouts.app-main')

@section('content')
@include('partials.alert')

<div class="row gy-2 gx-0 gx-lg-3">
    <div class="col-lg-4 d-flex flex-column">
        <div class="d-flex flex-nowrap align-items-center justify-content-between dropdown droptab droptab-lg">
            @php
                $activeText = optional($menus->where('id', '=', $activeMenu)->first())->text ?? '';
            @endphp
            <span class="fw-bold text-nowrap me-3 d-lg-none">{{ $activeText }}</span>
            <a href="javascript:;" class="d-flex align-items-center text-decoration-none d-lg-none" data-bs-toggle="dropdown" data-bs-target="#bonusMenu">
                <i class="icon-fa fa-solid fa-bars fs-5"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow-sm px-lg-2" id="bonusMenu">
                @foreach ($menus as $menu)
                    @if ($menu->type == 'separator')
                        <div class="dropdown-divider my-1"></div>
                    @elseif ($menu->type == 'menu')
                        <a class="dropdown-item @if($menu->id == $activeMenu) active @endif" href="{{ $menu->route }}">
                            {{ $menu->text }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="border-bottom d-lg-none flex-shrink-0 mt-1"></div>
    </div>
    <div class="col-lg-8">
        {!! $content !!}
    </div>
</div>
@endsection

@push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal', 'scrollable' => true])
@include('partials.modals.modal-sm', ['bsModalId' => 'my-modal-sm', 'scrollable' => true])
@endpush
