@php
    $canAction = hasPermission('main.settings.bonus.royalty.edit');
@endphp

<div class="row gx-2 gy-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between p-2">
                <span class="fw-bold">Internal</span>
                @if ($canAction)
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.royalty.edit') }}?mode=new&category=internal" title="Tambah">
                        <i class="fa-solid fa-plus me-1"></i>Tambah
                    </button>
                @endif
            </div>
            <div class="card-body p-2">
                <div class="d-block w-100 overflow-x-auto border">
                    <table class="table table-sm table-nowrap table-striped table-hover small mb-2" id="table">
                        @php
                            $anyInternal = $data->internal->isNotEmpty();
                        @endphp
                        <thead class="bg-gradient-brighten bg-white">
                            <tr class="text-center">
                                <th>Posisi</th>
                                <th class="border-start">Bonus (%)</th>
                                <th class="border-start">Target Omzet</th>
                                @if ($canAction && $anyInternal)
                                    <th class="border-start"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if ($anyInternal)
                                @foreach ($data->internal as $row)
                                <tr>
                                    <td>{{ $row->position->name }}</td>
                                    <td class="text-center">@formatAutoNumber($row->percent, false, 2)</td>
                                    <td class="text-center">{{ $row->target_omzet }}</td>
                                    @if ($canAction)
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.royalty.edit') }}?mode=edit&category=internal&id={{ $row->id }}" title="Edit">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td class="text-center" colspan="{{ $canAction ? 4 : 3 }}">{{ __('datatable.emptyTable') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between p-2">
                <span class="fw-bold">Eksternal</span>
                @if ($canAction)
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.royalty.edit') }}?mode=new&category=external" title="Tambah">
                        <i class="fa-solid fa-plus me-1"></i>Tambah
                    </button>
                @endif
            </div>
            <div class="card-body p-2">
                <div class="d-block w-100 overflow-x-auto border">
                    <table class="table table-sm table-nowrap table-striped table-hover small mb-2" id="table">
                        @php
                            $anyExternal = $data->internal->isNotEmpty();
                        @endphp
                        <thead class="bg-gradient-brighten bg-white">
                            <tr class="text-center">
                                <th>Posisi</th>
                                <th class="border-start">Bonus (%)</th>
                                <th class="border-start">Target Omzet</th>
                                @if ($canAction)
                                    <th class="border-start"></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @if ($anyExternal)
                                @foreach ($data->external as $row)
                                <tr>
                                    <td>
                                        {{ $row->position->name }} @if ($row->position_id == USER_INT_MGR) (Referral) @endif
                                    </td>
                                    <td class="text-center">@formatAutoNumber($row->percent, false, 2)</td>
                                    <td class="text-center">{{ $row->target_omzet }}</td>
                                    @if ($canAction)
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.royalty.edit') }}?mode=edit&category=external&id={{ $row->id }}" title="Edit">
                                                <i class="fa-solid fa-pencil-alt"></i>
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                                @endforeach
                            @else
                            <tr>
                                <td class="text-center" colspan="{{ $canAction ? 4 : 3 }}">{{ __('datatable.emptyTable') }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
