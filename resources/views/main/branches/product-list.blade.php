@php
    $canCreate = hasPermission('main.branch.product.create');
    $canStock = hasPermission('main.branch.product.stock');
    $canAction = $canStock;
@endphp

<table class="table table-sm small fs-auto" id="table">
    <thead class="d-none">
        <tr><th></th></tr>
    </thead>
    <tbody class="border-top-0">
        @php $noUrut = 1; @endphp
        @foreach ($datas as $row)
            <tr>
                <td class="p-0">
                    <div class="d-block text-wrap fw-bold bg-white px-2 py-1 border-bottom">
                        Cabang: {{ $row->branch->name }}
                    </div>
                    <table class="table table-sm table-hover table-nowrap mb-0" style="--table-bd-color:var(--bs-gray-400);">
                        <thead class="bg-gradient bg-light text-center text-wrap align-middle">
                            <tr>
                                <th rowspan="3"><i class="fa-solid fa-image fs-3"></i></th>
                                <th rowspan="3" class="border-start" style="--bd-start-color:var(--table-bd-color);">Produk</th>
                                <th colspan="3" class="border-start" style="--bd-start-color:var(--table-bd-color);">Sebelumnya</th>
                                <th colspan="6" class="border-start border-bottom" style="--bd-start-color:var(--table-bd-color); --bd-bottom-color:var(--table-bd-color);">
                                    Periode @formatFullDate($weekDate->start) s/d @formatFullDate($weekDate->end)
                                </th>
                                <th rowspan="3" class="border-start" style="--bd-start-color:var(--table-bd-color);">#</th>
                            </tr>
                            <tr>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Jumlah</th>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Output</th>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Sisa</th>
                                <th colspan="3" class="border-start border-bottom" style="--bd-start-color:var(--table-bd-color); --bd-bottom-color:var(--table-bd-color);">Input</th>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Jumlah</th>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Output</th>
                                <th rowspan="2" class="border-start" style="--bd-start-color:var(--table-bd-color);">Sisa</th>
                            </tr>
                            <tr>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Input</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Selisih</th>
                                <th class="border-start" style="--bd-start-color:var(--table-bd-color);">Admin</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-gray-700">
                            @if ($row->products->isNotEmpty())
                                @foreach ($row->products as $rowProduct)
                                    @php
                                        $category = $rowProduct->category;
                                        $product = $rowProduct->product;
                                        $stock = $rowProduct->stock;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            <img alt="Produk image" src="{{ $product->image_url }}" style="height: 50px; width:auto;">
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $category->name }}</div>
                                            <div>{{ $product->name }}</div>
                                        </td>
                                        <td class="text-end">@formatNumber($stock->last_stock)</td>
                                        <td class="text-end">@formatNumber($stock->output_stock)</td>
                                        <td class="text-end">@formatNumber($stock->last_stock - $stock->output_stock)</td>
                                        <td class="text-end">@formatNumber($stock->input_manager)</td>
                                        <td class="text-end">@formatNumber($stock->diff_stock)</td>
                                        <td class="text-end">@formatNumber($stock->input_admin)</td>
                                        <td class="text-end">@formatNumber($stock->total_stock)</td>
                                        <td class="text-end">@formatNumber($stock->current_output)</td>
                                        <td class="text-end">@formatNumber($stock->total_stock - $stock->current_output)</td>
                                        <td class="text-center border-start">
                                            <button type="button" class="btn btn-sm btn-outline-success" title="Atur Ulang Persediaan" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('main.branch.product.stock', ['branch' => $row->branch->id, 'product' => $product->id]) }}">
                                                <i class="fa-solid fa-cog"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center">Tidak ada data</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
    $(function() {
        $('#table').DataTable({
            dom: datatableDom,
            searching: false,
            info: false,
            lengthChange: false,
            sorting: false,
            pagingType: datatablePagingType,
            lengthMenu: [[1]],
            language: datatableLanguange,
            buttons: datatableButtons
        });

        customizeDatatable();
    });
</script>
