@php
    $canEdit = hasPermission('main.settings.bonus.sell.edit');
    $data = optional($setting);
@endphp

<div class="d-block text-center p-3 border rounded-3 bg-gray-100">
    <div class="d-block @if($canEdit) mb-3 mb-sm-4 mb-lg-5 @endif">
        <div class="fw-bold text-decoration-underline">Bonus</div>
        <div class="fs-1">@formatAutoNumber($data->percent ?? 0, false, 2)%</div>
    </div>

    @if ($canEdit)
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.sell.edit') }}" title="Edit">
        <i class="fa-solid fa-pencil-alt me-1"></i>Ubah
    </button>
    @endif
</div>

