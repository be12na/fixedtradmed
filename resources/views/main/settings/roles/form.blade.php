@php
    $canEdit = hasPermission('main.settings.roles.update');
    $mainIndex = 0;
@endphp

@csrf
<div class="accordion mb-3" id="accordionMainRole">
    @foreach ($roles as $mainRole)

    <div class="accordion-item">
        <h2 class="accordion-header" id="heading-{{ $mainRole->main_key }}">
            <button class="accordion-button p-2 fw-bold {{ ($mainIndex > 0) ? 'collapsed' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $mainRole->main_key }}" aria-expanded="true" aria-controls="collapseOne">
                {{ $mainRole->label }}
            </button>
        </h2>
        <div id="collapse-{{ $mainRole->main_key }}" class="accordion-collapse collapse {{ ($mainIndex == 0) ? 'show' : '' }}" aria-labelledby="heading-{{ $mainRole->main_key }}" data-bs-parent="#accordionMainRole">
            <div class="accordion-body p-2">
                
                @foreach ($mainRole->menus as $mainMenu)
                <div class="card">
                    <div class="card-header px-2 py-1 fw-bold">{{ $mainMenu->label }}</div>
                    <div class="card-body p-2">
                        <div class="d-flex flex-wrap">

                            @php
                                $mainIndex = $mainMenu->index_id;
                                $requireIndex = $mainMenu->required_index;
                            @endphp
                            
                            @foreach ($mainMenu->roles as $menuRole)

                            @php
                                $mainInputClass = '';
                                $mainTargetClass = '';
                                if ($requireIndex) {
                                    $mainInputClass = !$menuRole->is_index ? $mainIndex : 'can-disable';
                                    $mainTargetClass = $menuRole->is_index ? '.' . $mainIndex : '';
                                }
                            @endphp

                            <div class="form-check form-switch role flex-shrink-0">
                                <input class="form-check-input check-role {{ $mainInputClass }}" name="roles[]" type="checkbox" id="role-{{ $menuRole->input_id }}" value="{{ $menuRole->route }}" data-disable-target="{{ $mainTargetClass }}" autocomplete="off" @if($menuRole->has_role) checked @endif>
                                <label class="form-check-label" for="role-{{ $menuRole->input_id }}">{{ $menuRole->label }}</label>
                            </div>
                            
                                @if ($menuRole->sub_roles->isNotEmpty())

                                    @foreach ($menuRole->sub_roles as $subMenu)

                                    @php
                                        $subIndex = $subMenu->index_id;
                                        $requireSubIndex = $subMenu->required_index;
                                    @endphp
                                    
                                    <div class="flex-fill d-flex flex-wrap w-100 py-2 ps-3 border-top">
                                        <div class="role flex-shrink-0 fw-bold">
                                            {{ $subMenu->label }}
                                        </div>
                                        <div class="d-flex flex-fill flex-wrap ps-3">
                                            @foreach ($subMenu->roles as $subMenuRole)

                                                @php
                                                    $subInputClass = '';
                                                    $subTargetClass = '';
                                                    if ($requireSubIndex) {
                                                        $subInputClass = !$subMenuRole->is_index ? $subIndex : 'can-disable';
                                                        $subTargetClass = $subMenuRole->is_index ? '.' . $subIndex : '';
                                                    }
                                                @endphp

                                                <div class="form-check form-switch role flex-shrink-0">
                                                    <input class="form-check-input check-role {{ $mainIndex }} {{ $subInputClass }}" name="roles[]" type="checkbox" id="role-{{ $subMenuRole->input_id }}" value="{{ $subMenuRole->route }}" data-disable-target="{{ $subTargetClass }}" autocomplete="off" @if($subMenuRole->has_role) checked @endif>
                                                    <label class="form-check-label" for="role-{{ $subMenuRole->input_id }}">{{ $subMenuRole->label }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach

                                @endif
                            @endforeach

                        </div>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
    @php
        $mainIndex++;
    @endphp

    @endforeach
</div>
@if ($canEdit)
    <div class="d-block">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fa-solid fa-save me-1"></i>
            Simpan
        </button>
    </div>
@endif

