<div class="d-block mb-1 fw-bold">Cabang: {{ $branch->name }}</div>
<div class="accordion" id="accordionStock">
    @for ($n = 0; $n < count($weekRanges); $n++)
        @php
            $accId = "stock-{$n}";
            $headId = "head-stock-{$n}";
            $collapsed = ($n > 0) ? 'collapsed' : '';
            $show = ($n > 0) ? '' : 'show';
            $periode = app('neo')->dateRangeStockOpname($weekRanges[$n]);
            $action = (($n == 0) && hasPermission('main.branch.product.stock'));
            $stocks = $branch->stockProducts($weekRanges[$n], false);
        @endphp
        <div class="accordion-item">
            <div class="accordion-header" id="{{ $headId }}">
                <button class="accordion-button text-dark fs-auto fw-bold p-2 {{ $collapsed }}" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $accId }}" aria-expanded="true" aria-controls="{{ $accId }}">
                    Periode @formatFullDate($periode->start) s/d @formatFullDate($periode->end)
                </button>
            </div>
            <div id="{{ $accId }}" class="accordion-collapse collapse {{ $show }}" aria-labelledby="{{ $headId }}" data-bs-parent="#accordionStock">
                <div class="accordion-body p-0">
                    <table class="table table-sm table-nowrap mb-2 fs-auto" style="--table-bd-color:var(--bs-gray-400);">
                        <thead class="bg-gradient-brighten bg-white text-center align-middle">
                            <tr>
                                <th rowspan="2">Produk</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);" colspan="3">Input</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);" rowspan="2">Stock</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);" rowspan="2">Output</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);" rowspan="2">Balance</th>
                                @if ($action)
                                    <th class="border-start" style="--bd-start-color:var(--table-bd-color);" rowspan="2"></th>
                                @endif
                            </tr>
                            <tr>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Manager</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Selisih</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categories as $category)
                                <tr>
                                    <td colspan="8" class="fw-bold fs-auto bg-light">{{ $category->name }}</td>
                                </tr>
                                @foreach ($category->products as $product)
                                    @php
                                        $dataStock = $stocks->where('product_id', '=', $product->id)->first();
                                        $stock = optional(optional($dataStock)->selectedStock);
                                        $isBox = ($product->satuan == PRODUCT_UNIT_BOX);
                                        $propTotal = $isBox ? 'boxStock' : 'pcsStock';
                                        $propOut = $isBox ? 'boxOut' : 'pcsOut';
                                        $propBalance = $isBox ? 'boxBalance' : 'pcsBalance';
                                        $realStock = optional(optional($stock)->real_stock);
                                    @endphp
                                    <tr>
                                        <td class="ps-4">{{ $product->name }} ({{ \Arr::get(PRODUCT_UNITS, $product->satuan) }})</td>
                                        <td class="text-end">@formatNumber($stock->input_manager ?? 0)</td>
                                        <td class="text-end">@formatNumber(($stock->stock_type != STOCK_FLAG_MANAGER) ? 0 : ($stock->diff_stock ?? 0))</td>
                                        <td class="text-end">@formatNumber($stock->input_admin ?? 0)</td>
                                        <td class="text-end">@formatNumber($realStock->$propTotal ?? 0)</td>
                                        <td class="text-end">@formatNumber($realStock->$propOut ?? 0)</td>
                                        <td class="text-end">@formatNumber($realStock->$propBalance ?? 0)</td>
                                        @if ($action)
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-success" title="Atur Ulang Persediaan" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.branch.product.stock', ['branch' => $branch->id, 'product' => $product->id]) }}">
                                                    <i class="fa-solid fa-cog"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endfor
</div>

