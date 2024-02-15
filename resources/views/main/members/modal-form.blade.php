@php
    $showFooter = isset($showFooter) ? $showFooter : true;
    $showPosition = isset($showPosition) ? $showPosition : true;
@endphp

<form class="modal-content @if($disableSubmit) disable-submit @endif" method="POST" action="{{ $postUrl }}" id="myForm" data-alert-container="#alert-form-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ $modalHeader }} Anggota: @yield('modalTitle')</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body py-2 px-3 fs-auto">
        @csrf
        @if (!empty($values))
            @foreach ($values as $key => $value)
                @if (is_array($value))
                    @foreach ($value as $val)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $val }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endif
            @endforeach
        @endif
        <input type="hidden" name="form_step" value="@yield('stepName')">
        <div class="d-block" id="alert-form-container"></div>
        @if ($showPosition)
        <div class="text-nowrap mb-2 fw-bold border-bottom">Posisi: {{ $structure->name }}</div>
        @endif
        @yield('content')
    </div>
    @if ($showFooter === true)
    <div class="modal-footer justify-content-between py-1 px-3">
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid @yield('submitIcon') me-1"></i>
            @yield('submitText')
        </button>
    </div>
    @endif
</form>

@yield('modalScript')
