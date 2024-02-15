@php
    $teamLabel = isset($teamLabel) ? $teamLabel : 'Team';
    $containerClass = isset($containerClass) ? $containerClass : 'col-sm-6 col-md-3';
    $selectId = isset($selectId) ? $selectId : 'team-id';
    $defaultOption = isset($defaultOption) ? $defaultOption : [];
@endphp
<div class="{{ $containerClass }}">
    <div>{{ $teamLabel }}</div>
    <select class="form-select select2bs4 select2-custom" id="{{ $selectId }}">
        @if (!empty($defaultOption))
            <option value="{{ $defaultOption['id'] }}">{{ $defaultOption['text'] }}</option>
        @endif
    </select>
</div>