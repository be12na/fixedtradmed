@php
    $user = auth()->user();
    $userPackage = $user->userPackage;
@endphp
<form class="modal-content" method="POST" action="{{ route('mitra.package.ro.store') }}" id="myForm" data-alert-container="#alert-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">Repeat Order</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body">
        @csrf
        <div class="d-block" id="alert-container"></div>
        <div class="text-center">
            <div>Anda akan melakukan transaksi Repeat Order:</div>
            <div class="fw-bold">"{{ $userPackage->package_name }}"</div>
            <div class="mt-2">Lanjutkan?</div>
        </div>
    </div>
    <div class="modal-footer py-1 justify-content-center">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-handshake me-1"></i>
            Ya
        </button>
        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">
            <i class="fa-solid fa-time me-1"></i>
            Tidak
        </button>
    </div>
</form>