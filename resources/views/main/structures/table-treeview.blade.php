@extends('layouts.app-main')

@section('content')
<div class="d-block w-100 table-responsive border">
    <table class="table table-sm table-nowrap table-hover table-tree border-bottom mb-2" id="table">
        <thead class="bg-gradient-brighten bg-white small">
            <tr class="text-center">
                <th>Nama</th>
                <th class="border-start">Posisi</th>
                <th class="border-start">Omzet Jaringan</th>
                <th class="border-start">Status</th>
            </tr>
        </thead>
        <tbody class="small">
            {{ $rows }}
        </tbody>
    </table>
</div>
@endsection

{{-- @push('includeContent')
@include('partials.modals.modal', ['bsModalId' => 'my-modal'])
@endpush --}}

@push('styles')
<style>
    table.table-tree > tbody > tr > .cell-tree-node {
        padding: 0;
        position: relative;
    }
    table.table-tree > tbody > tr:not(.no-children) > .cell-tree-node {
        cursor: pointer;
    }
    table.table-tree > tbody > tr:not(.no-children) > .cell-tree-node:hover {
        color: var(--bs-primary);
    }
    table.table-tree > tbody > tr.no-children  > .cell-tree-node,
    table.table-tree > tbody > tr.no-children  > .cell-tree-node * {
        cursor: default;
    }
    table.table-tree > tbody > tr > td {
        border-bottom: none !important;
    }
    table.table-tree .tree-node-container {
        display: flex;
        position: relative;
        flex-wrap: nowrap;
        height: 100%;
        padding: 0.25rem 0.5rem 0.25rem calc(2rem + calc(1.5rem * var(--tree-node-level, 0)));
    }
    table.table-tree .tree-node-container > .icon-node {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        top: 7px;
        left: calc(2rem + calc(1.5rem * var(--tree-node-level, 0)) - 1.5rem);
        width: 16px;
        height: 16px;
        color: var(--bs-gray-600);
        text-decoration: none !important;
        border: 1px solid var(--bs-gray-600);
        z-index: 10;
    }
    table.table-tree .tree-node-container > .icon-node::before {
        content: "\f0fe";
    }
    table.table-tree > tbody > tr.expanded .icon-node::before {
        content: "\f146";
    }
    table.table-tree > tbody > tr.no-children .icon-node {
        border: none;
        pointer-events: none;
    }
    table.table-tree > tbody > tr.no-children .icon-node::before {
        content: "\f192";
    }
</style>
@endpush

@push('scripts')
<script>
    function expandDownlines(row)
    {
        if (!row.hasClass('loaded')) {
            $.get({
                url: '{{ route("main.member.structure.table") }}',
                data: {
                    'root_id': row.data('root-id'),
                    'parent_id': row.data('member-id'),
                },
            }).done(function(response) {
                row.addClass('expanded loaded').after(response);
            });
        } else {
            const target = row.data('children');
            const isExpanded = row.hasClass('expanded');
            if (isExpanded) {
                $('tr' + target).addClass('d-none');
            } else {
                let cps = [];
                $('tr' + target).each(function() {
                    const tr = $(this);
                    if (!tr.hasClass('expanded')) {
                        cps.push(tr);
                    }
                    tr.removeClass('d-none');
                });
                if (cps.length > 0) {
                    $.each(cps, function(index, elem) {
                        $('tr' + elem.data('children')).addClass('d-none');
                    });
                }
            }
            row.toggleClass('expanded');
        }
    }

    $(function() {
        $(document).on('click', '#table > tbody > tr:not(.no-children) > .cell-tree-node', function(e) {
            const me = $(this);
            const row = me.parents('tr');
            expandDownlines(row);
        });
        
        $('#table > tbody > tr:not(.no-children) > .cell-tree-node').trigger('click');
    });
</script>
@endpush
