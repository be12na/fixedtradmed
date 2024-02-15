<div class="modal fade" id="{{ isset($bsModalId) ? $bsModalId : 'myModalSM' }}" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered {{ (isset($scrollable) && $scrollable === true) ? 'modal-dialog-scrollable' : '' }}" id="{{ (isset($bsModalId) ? $bsModalId : 'myModalSM') . '-dialog' }}">
    </div>
</div>