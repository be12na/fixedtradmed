@php
    $canCreate = hasPermission('main.master.product.create');
    $canEdit = hasPermission('main.master.product.edit');
    $readDiscount = hasPermission('main.master.product.discount.index');
    $readReward = hasPermission('main.master.product.reward.index');
    $readBonus = hasPermission('main.master.product.bonus.index');
    $readSetting = false; // ($readDiscount || $readReward || $readBonus);
    $countProducts = 0;
@endphp

@foreach ($categories as $category)
    @foreach ($category->products->sortBy('package_range')->values() as $row)
        @php
            $countProducts++;
        @endphp
        <div class="col-6 col-sm-4 col-md-3 col-lg-2 d-flex">
            <div
                class="flex-fill d-flex flex-column border rounded-3 bg-light text-center shadow-sm cursor-default hover-shadow position-relative">
                {{-- @if ($canEdit)
                    <div class="d-flex flex-column position-absolute top-0 end-0 p-2">
                        <button class="btn btn-xs btn-success" title="Edit" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.master.product.edit', ['product' => $row->id]) }}">
                            <i class="fa-solid fa-pencil-alt"></i>
                        </button>
                    </div>
                @endif --}}
                <div class="d-flex justify-content-center w-100 px-2 pt-2 mb-2 overflow-hidden" style="height:80px;">
                    <img alt="{{ $row->code }} image" src="{{ $row->image_url }}" style="height: 100%; width:auto;">
                </div>
                <div class="flex-fill fs-auto px-2 mb-2">
                    {{-- <div class="fw-bold">{{ $category->name }}</div> --}}
                    {{-- <div class="fw-bold">{{ $category->merek }}</div> --}}
                    {{-- <div class="fw-bold">{{ $row->code }}</div> --}}
                    <div>{{ $row->name }}</div>
                </div>
                @if ($canEdit || $readSetting)
                    <div class="mt-2 border-top d-flex justify-content-around">
                        @if ($canEdit)
                            <div class="py-2">
                                <button class="btn btn-xs btn-success" title="Edit" data-bs-toggle="modal"
                                    data-bs-target="#my-modal"
                                    data-modal-url="{{ route('main.master.product.edit', ['product' => $row->id]) }}">
                                    <i class="fa-solid fa-pencil-alt"></i>
                                </button>
                            </div>
                        @endif
                        @if ($readSetting)
                            @php
                                $dropdownId = "dropdown-product-{$row->id}";
                            @endphp
                            <div class="dropdown py-2">
                                <button class="btn btn-xs btn-info" title="Setting" data-bs-toggle="dropdown"
                                    data-bs-target="#{{ $dropdownId }}">
                                    <i class="fa-solid fa-cog"></i>
                                </button>
                                <div class="dropdown-menu">
                                    @if ($readDiscount)
                                        <a class="dropdown-item"
                                            href="{{ route('main.master.product.discount.index', ['product' => $row->id]) }}">
                                            Diskon
                                        </a>
                                    @endif
                                    @if ($readReward)
                                        <a class="dropdown-item"
                                            href="{{ route('main.master.product.reward.index', ['product' => $row->id]) }}">
                                            Reward
                                        </a>
                                    @endif
                                    @if ($readBonus)
                                        <a class="dropdown-item"
                                            href="{{ route('main.master.product.bonus.index', ['product' => $row->id]) }}">
                                            Bonus
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @endforeach
@endforeach

@if ($countProducts == 0 || $canCreate)
    <div class="col-6 col-sm-auto">
        <div class="d-flex flex-column align-items-center justify-content-center h-100">
            @if ($countProducts == 0)
                <div class="text-center mb-3">Tidak ada data produk</div>
            @endif

            @if ($canCreate)
                <div class="text-center">
                    <button type="button" class="btn btn-outline-primary" title="Tambah" data-bs-toggle="modal"
                        data-bs-target="#my-modal"
                        data-modal-url="{{ route('main.master.product.create', ['categoryId' => $categoryId]) }}">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            @endif
        </div>
    </div>
@endif
