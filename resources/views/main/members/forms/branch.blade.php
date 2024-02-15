@extends('main.members.modal-form')

@php
    $hasData = !empty($data);
    $data = optional($data);
    $dataId = $data->id ?? 0;
    if (!isset($currentBranchIds)) $currentBranchIds = [];
    $typeCheck = (in_array($structure->id, [USER_INT_MGR, USER_INT_AM])) ? 'checkbox' : 'radio';
    $selectMgrType = ($structure->id == USER_INT_MGR);
@endphp

@section('modalTitle', 'Pilih Cabang')
@section('stepName', 'branch')
@section('submitIcon', 'fa-arrow-right')
@section('submitText', 'Lanjut')

@section('content')
@if ($hasData)
<div class="d-block mb-3 text-nowrap">
    <span class="d-inline-block fw-bold me-1">Member</span>
    <span class="d-inline-block me-1">:</span>
    <span class="d-inline-block text-wrap">{{ $data->name }}</span>
</div>
@endif
<div class="d-block w-100 border overflow-x-auto">
    <table class="table table-sm table-nowrap table-striped table-hover mb-2">
        <thead class="bg-gradient-brighten bg-white fs-auto">
            <tr class="text-center">
                <th></th>
                <th class="border-start">Cabang</th>
            </tr>
        </thead>
        <tbody class="small">
            @if ($branches->isNotEmpty())
                @foreach ($branches as $branch)
                    <tr>
                        @php
                            $chekId = 'branch-' . $branch->id;
                            $currentMgrType = null;
                            $currentPositionExt = null;
                            if (in_array($branch->id, $currentBranchIds)) {
                                $branchMember = $branch->members->where('user_id', '=', $dataId)->first();
                                $currentMgrType = $branchMember ? $branchMember->manager_type : null;
                                $currentPositionExt = $branchMember ? $branchMember->position_ext : null;
                            }
                        @endphp
                        <td class="text-center">
                            <input type="{{ $typeCheck }}" class="check-branch" id="{{ $chekId }}" name="branch_checks[]" value="{{ $branch->id }}" @if(in_array($branch->id, $currentBranchIds)) checked @endif>
                            <input type="hidden" class="branch-value" name="branch_ids[]" value="{{ $branch->id }}">
                        </td>
                        <td>
                            <label for="{{ $chekId }}" class="d-block cursor-pointer mb-1 fw-bold">
                                {{ $branch->name }}
                            </label>
                            @if ($selectMgrType)
                                <div class="row g-1">
                                    <div class="col-sm-6">
                                        <select class="d-none form-select select-branch-manager">
                                            <option value="">-- Pilih Posisi --</option>
                                            @foreach (app('appStructure')->getExternalManagerOptions() as $idExt => $nameExt)
                                                <option value="{{ $idExt }}" @optionSelected($idExt, $currentPositionExt)>{{ $nameExt }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" class="select-value" name="branch_positions[]" value="{{ $currentPositionExt }}">
                                    </div>
                                    <div class="col-sm-6">
                                        <select class="d-none form-select select-branch-manager">
                                            @foreach (USER_BRANCH_MANAGER_TYPES as $typeId => $typeName)
                                                <option value="{{ ($typeId > 0) ? $typeId : '' }}" @optionSelected($typeId, $currentMgrType)>{{ $typeName ?? '-- Pilih Jenis Manager --' }}</option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" class="select-value" name="branch_types[]" value="{{ $currentMgrType }}">
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td class="text-center">{{ __('datatable.emptyTable') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
@endsection

@section('modalScript')
<script>
    $(function() {
        $('.select-branch-manager').on('change', function(e) {
            const me = $(this);
            me.closest('div').find('.select-value').val(me.val());
        });
        
        const checkBranch = $('.check-branch');
        checkBranch.on('change', function() {
            const me = $(this);
            const trParent = me.closest('tr');
            const select = $('td .select-branch-manager', trParent);

            if (me.is(':checked')) {
                select.removeClass('d-none');
            } else {
                select.val('').addClass('d-none').change();
            }
        }).change();
    });
</script>
@endsection
