@php
    $saleItem = optional($saleItem);
@endphp
<tr class="item-row">
    <td>
        <div class="d-block" style="min-width:200px;">
            <select name="product_ids[]" class="form-select select-product" autocomplete="off">
                <option value="" data-zone-barat="0" data-zone-timur="0">-- Pilih Produk --</option>
                @foreach ($itemsProduct as $row)
                    <optgroup label="{{ $row->category->name }}">
                        @foreach ($row->products as $product)
                            <option value="{{ $product->id }}" data-zone-barat="{{ $product->harga_a }}" data-zone-timur="{{ $product->harga_b }}" data-profit-crew="{{ $product->komisi }}" @optionSelected($product->id, $saleItem->product_id ?? 0)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </td>
    <td>
        <div class="d-block" style="min-width:100px;">
            <input type="number" class="form-control sale-item-qty" min="1" step="1" name="product_qty[]" value="{{ $saleItem->product_qty ?? 1 }}" autocomplete="off">
        </div>
    </td>
    <td>
        <div class="d-block item-price text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-amount text-end" style="min-width:100px;"></div>
    </td>
    <td class="text-end">
        <button type="button" class="btn btn-sm btn-danger btn-remove-item">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>
