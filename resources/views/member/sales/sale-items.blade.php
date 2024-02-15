@php
    $saleItem = optional($saleItem);
    $zones = neo()->zones(true);
@endphp
<tr class="item-row">
    <td>
        <div class="d-block" style="min-width:200px;">
            <select name="product_ids[]" class="form-select select-product" data-unit-box="{{ PRODUCT_UNIT_BOX }}" data-unit-pcs="{{ PRODUCT_UNIT_PCS }}" autocomplete="off">
                <option value="" data-zone-barat="0" data-zone-timur="0" data-eceran-zone-barat="0" data-eceran-zone-timur="0" data-eceran="0" 
                @foreach ($zones as $zone)
                    data-zone-{{ $zone->id }}="0" data-eceran-zone-{{ $zone->id }}="0" 
                @endforeach
                >-- Pilih Produk --</option>

                @foreach ($itemsProduct as $row)
                    <optgroup label="{{ $row->category->name }}">
                        @foreach ($row->products as $product)
                            <option value="{{ $product->id }}" data-zone-barat="{{ $product->harga_a }}" data-zone-timur="{{ $product->harga_b }}" data-eceran-zone-barat="{{ $product->eceranZonePrice(ZONE_WEST) }}" data-eceran-zone-timur="{{ $product->eceranZonePrice(ZONE_EAST) }}" data-eceran="{{ ($product->satuan == PRODUCT_UNIT_BOX) ? 1 : 0 }}" 
                                @foreach ($zones as $zone)
                                    data-zone-{{ $zone->id }}="{{ $product->zonePriceV2($zone->id) }}" data-eceran-zone-{{ $zone->id }}="{{ $product->eceranZonePriceV2($zone->id) }}" 
                                @endforeach
                                @optionSelected($product->id, $saleItem->product_id ?? 0)>
                                {{ $product->name }} ({{ ucwords(strtolower($product->product_unit)) }})
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
        <div class="d-block item-unit" style="min-width:100px;">
            <select class="form-select unit-select" name="product_units[]" autocomplete="off">
                @foreach (PRODUCT_UNITS as $key => $name)
                    <option value="{{ $key }}">{{ strtoupper($name) }}</option>
                @endforeach
            </select>
            <div class="unit-pcs text-center lh-lg">
                {{ strtoupper(\Arr::get(PRODUCT_UNITS, PRODUCT_UNIT_PCS)) }}
            </div>
        </div>
    </td>
    <td>
        <div class="d-block item-price lh-lg text-end" style="min-width:100px;"></div>
    </td>
    <td>
        <div class="d-block item-amount lh-lg text-end" style="min-width:100px;"></div>
    </td>
    <td class="text-end">
        <button type="button" class="btn btn-sm btn-danger btn-remove-item">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>
