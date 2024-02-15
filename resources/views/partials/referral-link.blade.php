@if ($refLinkMode == 'html')
<div class="d-block p-3 border rounded-3 bg-light bg-gradient shadow-sm hover-shadow">
    @php
        $user = Auth::user();
        $link = $user->is_manager_user ? $user->mitra_referral_url : $user->mitra_store_url;
    @endphp
    <div class="d-flex flex-column flex-sm-row align-items-center align-items-sm-end mb-2">
        <div class="text-center me-0 me-sm-3 mb-2 mb-sm-0">
            <i class="fs-1 fa-solid fa-share-nodes"></i>
        </div>
        <div class="flex-fill d-block position-relative">
            <span class="copy-clipboard bg-dark text-light" id="copyClipboard">
                Sudah di-copy ke clipboard
            </span>
            <button type="button" class="btn btn-outline-dark" id="btn-copy-reflink" title="Copy" onclick="">
                <i class="fa fa-copy"></i>
            </button>
            <a href="whatsapp://send?text={{ $link }}" target="_blank" class="btn btn-outline-success" title="Share to whatsapp">
                <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ $link }}" class="btn btn-outline-primary" title="Share to facebook">
                <i class="fa-brands fa-facebook"></i>
            </a>
        </div>
    </div>
    <div class="fs-auto text-center text-sm-start" id="reflink-text">{{ $link }}</div>
</div>
@elseif ($refLinkMode == 'script')
<style>
    .copy-clipboard {
        position: absolute;
        padding: 0.25rem 0.5rem;
        bottom: calc(100% + 5px);
        left: 0;
        border-radius: 1rem;
        opacity: 1;
        transition: opacity 1.5s ease;
    }
    .copy-clipboard::after {
        content: '';
        position: absolute;
        bottom: -3px;
        left: 15px;
        padding: 5px;
        border: 4px solid var(--bs-dark);
        border-top: none;
        border-left: none;
        z-index: 1;
        transform: rotate(45deg);
    }
    .copy-clipboard:not(.show) {
        display: none;
    }
    .copy-clipboard.hidding {
        opacity: 0;
    }
</style>
<script>
    let copyClipboard;
    function copySuccess()
    {
        copyClipboard.addClass('show');

        setTimeout(() => {
            copyClipboard.addClass('hidding');

            setTimeout(() => {
                copyClipboard.removeClass('show hidding');
            }, 1500);
        }, 3000);
    }
    $(function() {
        copyClipboard = $('#copyClipboard');

        $('#btn-copy-reflink').on('click', function() {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($('#reflink-text').text()).select();
            document.execCommand("copy");
            $temp.remove();
            copySuccess()
        });
    });
</script>
@endif
