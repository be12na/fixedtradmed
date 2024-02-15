@extends('layouts.app-main')

@php
    $canCreate = hasPermission('main.master.product-category.create');
    $canEdit = hasPermission('main.master.product-category.edit');
    $canAction = $canEdit;
@endphp

@section('content')
@include('partials.alert')
<div class="d-block">
    <div class="row" style="--bs-gutter-y:0.5rem; --bs-gutter-x:1rem;">
        @foreach ($categories as $row)
            <div class="col-6 col-sm-4 col-lg-3">
                <div class="d-flex flex-column h-100 px-2 py-3 border rounded-3 bg-light bg-gradient text-center shadow-sm cursor-default hover-shadow">
                    <div class="fw-bold text-decoration-underline fs-auto">{{ $row->code }}</div>
                    <div class="fw-bold fs-auto">{{ $row->merek }}</div>
                    <div class="flex-fill d-flex align-items-center justify-content-center text-center mb-2 fs-auto">
                        {{ $row->name }}
                    </div>
                    @if ($canAction)
                        <div>
                            @if ($canEdit)
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.master.product-category.edit', ['productCategory' => $row->id]) }}">
                                    <i class="fa-solid fa-pencil-alt me-2"></i>
                                    Edit
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach

        @if ($categories->isEmpty() || $canCreate)
            <div class="col-auto">
                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                    @if ($categories->isEmpty())
                        <div class="text-center mb-3">Tidak ada data kategori produk</div>
                    @endif

                    @if ($canCreate)
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.master.product-category.create') }}">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal'])
@endpush

