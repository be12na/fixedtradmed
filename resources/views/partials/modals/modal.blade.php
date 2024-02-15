<div class="modal fade" id="{{ isset($bsModalId) ? $bsModalId : 'myModal' }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered {{ (isset($scrollable) && $scrollable === true) ? 'modal-dialog-scrollable' : '' }}" id="{{ (isset($bsModalId) ? $bsModalId : 'myModal') . '-dialog' }}">
    </div>
</div>