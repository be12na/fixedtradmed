<a class="d-flex flex-nowrap text-decoration-none link-dark" href="{{ route('mitraStore.index', ['mitraStore' => $user->username]) }}" target="_blank">
    <div class="flex-shrink-0 me-2 pt-2" style="flex-basis:20px;">
        <div class="d-inline-block p-3 rounded-circle lh-1 reseller-box">
            <i class="fa fa-user"></i>
        </div>
    </div>
    <div class="flex-fill p-2 p-lg-3 box-rounded reseller-box">
        <div class="d-flex justify-content-between flex-nowrap mb-2">
            <div class="d-flex justify-content-center align-items-end flex-nowrap">
                <span class="fw-bold me-3">{{ $user->name }}</span>
                <span class="flex-grow-0 py-1 px-2 bg-info rounded-circle lh-1">
                    <i class="fa fa-check"></i>
                </span>
            </div>
            <span class="fa fa-cart-shopping"></span>
        </div>
        <div>
            {{ $user->complete_address }}
        </div>
    </div>
</a>
