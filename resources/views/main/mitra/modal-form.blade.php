@php
    $showFooter = isset($showFooter) ? $showFooter : true;
    $showName = isset($showName) ? $showName : true;
    $canSubmit = isset($canSubmit) ? $canSubmit : false;
@endphp

<form class="modal-content @if($disableSubmit) disable-submit @endif" method="POST" action="{{ $submitUrl }}" id="myForm" data-alert-container="#alert-form-container">
    <div class="modal-header py-2 px-3">
        <span class="fw-bold small">{{ (isset($modalHeader) && $modalHeader) ? $modalHeader . ' ' : '' }}Member: @yield('modalTitle')</span>
        <button type="button" class="btn-close lh-1" data-bs-dismiss="modal" style="background: none; font-size:12px;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
    <div class="modal-body py-2 px-3">
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
        @if ($showName && !empty($data))
            <div class="d-block mb-3 text-nowrap">
                <span class="d-inline-block fw-bold me-1">Member</span>
                <span class="d-inline-block me-1">:</span>
                <span class="d-inline-block text-wrap">{{ $data->name }}</span>
            </div>
        @endif
        @yield('content')
    </div>
    @if ($showFooter === true)
    @php
        $footerClass = isset($footerClass) ? $footerClass : '';
    @endphp
    <div class="modal-footer {{ $footerClass }} py-1 px-3">
        @if ($canSubmit === true)
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid @yield('submitIcon') me-1"></i>
            @yield('submitText')
        </button>
        @endif
        <button type="button" class="btn btn-sm btn-warning" data-bs-dismiss="modal">
            <i class="fa-solid fa-undo me-1"></i>
            Batal
        </button>
    </div>
    @endif
</form>

@yield('modalScript')
