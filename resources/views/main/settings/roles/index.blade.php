@extends('layouts.app-main')

@section('content')
<div class="d-block" id="alert-role-container">
    @include('partials.alert')
</div>

<div class="datatable-controls d-flex flex-wrap mb-3 border-bottom">
    <div class="d-block me-1 mb-1" style="max-width: 100%;">
        <select class="form-select form-select-sm" id="type-divisi" autocomplete="off">
            @foreach ($positions as $key => $value)
                @if (is_array($value))
                    <optgroup label="{{ $value[0] }}">
                        @foreach ($value[1] as $idValue => $nameValue)
                            <option value="{{ $idValue }}" @optionSelected($idValue, $currentAdminId)>{{ $nameValue }}</option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $key }}" @optionSelected($key, $currentAdminId)>{{ $value }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="dt-buttons">
        <button type="button" class="dt-button buttons-html5" id="btn-refresh" title="Refresh">
            <i class="fa-solid fa-rotate"></i>
        </button>
    </div>
</div>

@hasPermission('main.settings.roles.update')
<input type="hidden" id="status" value="1">
<form class="disable-submit form-role" id="role-view" method="POST" action="{{ route('main.settings.roles.update') }}" data-alert-container="#alert-role-container"></form>
@else
<input type="hidden" id="status" value="0">
<div class="d-block fs-auto" id="role-view"></div>
@endhasPermission

@endsection

@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
@endpush

@push('styles')
<style>
    .accordion-body > .card:not(:last-child) {
        margin-bottom: 0.5rem;
    }
    .role {
        min-width: 300px;
        font-weight: 600;
        margin-right: 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
    let roleView;

    function disableCheck(obj)
    {
        let target = obj.data('disable-target');

        if ((target != undefined) && (target != '')) {
            target = $(target);
            target.prop('disabled', !obj.is(':checked'));
            $.each(target, function(k, v) {
                const me = $(v);
                if (me.hasClass('can-disable') && !me.is(':disabled')) {
                    me.change();
                }
            });
        }
    }

    function reloadRole()
    {
        const value = $('#type-divisi').val();
        const status = $('#status').val();
        roleView.empty();

        showMainProcessing();

        $.get({
            url: '{{ route("main.settings.roles.form") }}',
            data: {type_division: value}
        }).done(function(respon) {
            roleView.html(respon);
            if (status == 1) {
                $('.check-role.can-disable').change();
            } else {
                $('.check-role').prop('disabled', true);
            }
        }).fail(function(respon) {
            roleView.html(respon.responseText);
        }).always(function(respon) {
            stopMainProcessing();
        });
    }

    $(function() {
        roleView = $('#role-view');

        $(document).on('submit', '.form-role', function(e) {
            const frm = $(this);
            const data = frm.serialize();
            const url = frm.attr('action');
            const msg = $(frm.data('alert-container'));
            showMainProcessing();
            $.post({
                url: url,
                data: data
            }).done(function(respon) {
                $('#alert-role-container').empty().html(respon);
                reloadRole();
            }).fail(function(respon) {
                msg.empty().html(respon.responseText);
            }).always(function(respon) {
                stopMainProcessing();
            });

            return false;
        }).on('change', '.check-role.can-disable', function(e) {
            disableCheck($(this));
        });

        $('#type-divisi').on('change', function(e) {
            reloadRole();
        })

        $('#btn-refresh').on('click', function(e) {
            reloadRole();
        }).click();
    });
</script>
@endpush
