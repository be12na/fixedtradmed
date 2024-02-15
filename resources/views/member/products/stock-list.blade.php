<table class="table table-sm table-nowrap table-striped table-hover" id="table">
    <thead class="bg-gradient-brighten bg-white small">
        <tr class="text-center">
            <th><i class="fa-solid fa-image"></i></th>
            <th class="border-start">Kategori / Merek</th>
            <th class="border-start">Produk</th>
            <th class="border-start">Jumlah</th>
            <th class="border-start"></th>
        </tr>
    </thead>
    <tbody class="fs-auto border-top-gray-700 small">
        @if ($products->isNotEmpty())
            @foreach ($products as $rowProduct)
                @php
                    $category = $rowProduct->category;
                    $product = $rowProduct->product;
                @endphp
                <tr>
                    <td class="text-center">
                        <img alt="Produk image" src="{{ $product->image_url }}" style="height: 50px; width:auto;">
                    </td>
                    <td>
                        <div class="fw-bold">{{ $category->name }}</div>
                        <div class="fw-bold">{{ $product->name }}</div>
                    </td>
                    <td>{{ $product->name }}</td>
                    <td class="text-end">@formatNumber($rowProduct->countProduct)</td>
                    <td class="text-center">
                        @if ($rowProduct->canInput)
                            <button type="button" class="btn btn-sm btn-outline-success" title="Atur Ulang Persediaan" data-bs-toggle="modal" data-bs-target="#my-modal" data-modal-url="{{ route('member.product.stock.input', ['branch' => $branch->id, 'product' => $product->id]) }}">
                                <i class="fa fa-check-to-slot"></i>
                            </button>
                        @endif
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

<script>
    $(function() {
        $('#table').DataTable({
            dom: datatableDom,
            info: false,
            lengthChange: false,
            sorting: false,
            paging: false,
            language: datatableLanguange,
            buttons: datatableButtons,
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [0, 4]}
            ]
        });

        customizeDatatable();
    });
</script>
