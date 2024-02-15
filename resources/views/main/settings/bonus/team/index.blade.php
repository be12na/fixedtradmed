@php
    $canAction = hasPermission('main.settings.bonus.team.edit');
@endphp

@if ($canAction)
<div class="d-flex align-items-center justify-content-end mb-2">
    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.team.edit') }}?mode=new" title="Tambah">
        <i class="fa-solid fa-plus me-1"></i>Tambah
    </button>
</div>
@endif

<div class="d-block w-100 overflow-x-auto border">
    <table class="table table-sm table-nowrap table-striped table-hover small mb-2" id="table">
        @php
            $anyData = $settings->isNotEmpty();
        @endphp
        <thead class="bg-gradient-brighten bg-white">
            <tr class="text-center">
                <th>Posisi</th>
                <th class="border-start">Bonus (%)</th>
                @if ($canAction && $anyData)
                    <th class="border-start"></th>
                @endif
            </tr>
        </thead>
        <tbody>
            @if ($anyData)
                @foreach ($settings as $row)
                <tr>
                    <td>{{ $row->position->name }}</td>
                    <td class="text-center">@formatAutoNumber($row->percent, false, 2)</td>
                    @if ($canAction)
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#my-modal-sm" data-modal-url="{{ route('main.settings.bonus.team.edit') }}?mode=edit&id={{ $row->id }}" title="Edit">
                                <i class="fa-solid fa-pencil-alt"></i>
                            </button>
                        </td>
                    @endif
                </tr>
                @endforeach
            @else
            <tr>
                <td class="text-center" colspan="{{ $canAction ? 3 : 2 }}">{{ __('datatable.emptyTable') }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
