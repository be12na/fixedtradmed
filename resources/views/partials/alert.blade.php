@php
    $pesan = isset($message) ? $message : session()->pull('message');
    $pesanClass = isset($messageClass) ? $messageClass : session()->pull('messageClass');
@endphp

@if ($pesan && $pesanClass)
@php
    $alertClass = ($pesanClass == 'error') ? 'danger' : $pesanClass;
    $btnClose = isset($showBtnClose) ? ($showBtnClose === true) : true;

    if ($alertClass == 'danger') {
        $iconClass = 'times-circle';
    } elseif ($alertClass == 'success') {
        $iconClass = 'check-circle';
    } elseif ($alertClass == 'warning') {
        $iconClass = 'exclamation-circle';
    } elseif ($alertClass == 'info') {
        $iconClass = 'info-circle';
    } else {
        $iconClass = 'question-circle';
    }
@endphp
<div class="alert alert-{{ $alertClass }} alert-dismissible p-2">
    <div class="d-flex">
        <i class="flex-grow-0 flex-shrink-0 fs-3 fa-solid fa-{{ $iconClass }}"></i>
        <div class="flex-fill mx-2">
            {!! $pesan !!}
        </div>
        @if ($btnClose)
        <button type="button" class="btn-close p-0 position-static flex-grow-0 flex-shrink-0 lh-1" data-bs-dismiss="alert" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
        @endif
    </div>
</div>
@endif

