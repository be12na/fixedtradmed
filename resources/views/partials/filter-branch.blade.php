@php
    $currentBranchId = isset($currentBranchId) ? $currentBranchId : -1;
    $containerClass = isset($containerClass) ? $containerClass : 'col-sm-6 col-md-3';
@endphp
<div class="{{ $containerClass }}">
    <div>Cabang</div>
    <select class="form-select" id="branch-id" autocomplete="off">
        <option value="-1">-- Pilih Cabang --</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" @optionSelected($branch->id, $currentBranchId)>{{ $branch->name }}</option>
        @endforeach
    </select>
</div>