<div class="modal fade" id="{{ isset($bsModalId) ? $bsModalId : 'myModalXL' }}" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered {{ (isset($scrollable) && $scrollable === true) ? 'modal-dialog-scrollable' : '' }}" id="{{ (isset($bsModalId) ? $bsModalId : 'myModalXL') . '-dialog' }}">
    </div>
</div>