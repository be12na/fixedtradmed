<div class="modal fade" id="{{ isset($bsModalId) ? $bsModalId : 'myModalLG' }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered {{ (isset($scrollable) && $scrollable === true) ? 'modal-dialog-scrollable' : '' }}" id="{{ (isset($bsModalId) ? $bsModalId : 'myModalLG') . '-dialog' }}">
    </div>
</div>