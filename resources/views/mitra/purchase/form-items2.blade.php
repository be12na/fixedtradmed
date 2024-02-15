@php
    $purchaseItem = optional($purchaseItem);
    $zones = neo()->zones(true);
@endphp
<tr class="item-row">
    <td>
        <div class="d-block" style="min-width:200px;">
            <select name="product_ids[]" class="form-select select-product" autocomplete="off">
                <option value="0">-- Pilih Produk --</option>
                @foreach ($itemsProduct as $row)
                    <optgroup label="{{ $row->category->name }}">
                        @foreach ($row->products as $product)
                            <option value="{{ $product->id }}" @optionSelected($product->id, $saleItem->product_id ?? 0)>
                                {{ $product->name }} ({{ ucwords(strtolower($product->product_unit)) }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>
    </td>
    <td>
        <div class="d-block box-qty" style="min-width:100px;">
            {{-- <input type="number" class="form-control sale-item-qty" min="1" step="1" name="product_qty[]" value="{{ $purchaseItem->product_qty ?? 1 }}" autocomplete="off"> --}}
        </div>
    </td>
    <td>
        <div class="d-block item-normal-price text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-promo-price text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-discount text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-price text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-special-discount text-end" style="min-width:100px;"></div>
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
