@php
    if (!isset($controlClass)) $controlClass = '';
    if (!isset($controlWidth)) $controlWidth = '200px';
@endphp
<div class="d-block" style="width:{{ $controlWidth }};">
    <div class="d-flex align-items-end flex-nowrap mb-2">
        <img src="{{ captcha_src() }}" class="flex-fill me-2" id="img-captcha">
        <button type="button" class="btn btn-sm btn-glow btn-captcha fw-bold" tabindex="-1">
            <i class="bi bi-arrow-repeat"></i>
        </button>
    </div>
    <input type="text" class="form-control {{ $controlClass }}" name="captcha" autocomplete="off" required>
</div>