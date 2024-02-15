@foreach ($products as $row)
    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
        <div class="d-flex flex-column h-100 border rounded-3 bg-light text-center shadow-sm cursor-default hover-shadow position-relative">
            <div class="d-flex justify-content-center w-100 px-2 pt-2 mb-2 overflow-hidden" style="height:80px;">
                <img alt="Produk image" src="{{ $row->image_url }}" style="height: 100%; width:auto;">
            </div>
            <div class="flex-fill px-2 mb-2">
                <div>{{ $row->name }}</div>
            </div>
        </div>
    </div>
@endforeach
