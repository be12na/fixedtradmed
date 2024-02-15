@php
    $responsiveElement = isset($datatableResponsive) ? $datatableResponsive : 'lg';
    
    $footerDom = '<"datatable-paging-info d-flex flex-column flex-md-row"<"flex-fill d-flex justify-content-center justify-content-md-start align-items-center"i><"flex-fill d-flex justify-content-center justify-content-md-end"p>>';
    
    $scrollable = isset($scrollable) ? (bool) $scrollable : true;
    $scrollClass = $scrollable ? 'table-responsive' : '';
    $tableDom = "r<\"datatable-table {$scrollClass}\"t>";
    $defaultControlDom = "<\"datatable-controls d-flex flex-column flex-{$responsiveElement}-row\"<\"d-flex flex-fill flex-column flex-{$responsiveElement}-row\"l<\"ms-0 ms-{$responsiveElement}-auto\"f>>>";

    $buttonControlsDom = "<\"datatable-controls d-flex flex-column flex-{$responsiveElement}-row\"<\"d-flex align-items-center me-0 me-{$responsiveElement}-2 \"B><\"d-flex flex-fill flex-column flex-{$responsiveElement}-row\"l<\"ms-0 ms-{$responsiveElement}-auto\"f>>>";

    $customId = 'dt-buttons-' . mt_rand(10001, 99999);

    $customButtonControlsDom = "<\"datatable-controls d-flex flex-column flex-{$responsiveElement}-row\"<\"d-flex flex-wrap me-0 me-{$responsiveElement}-2 \"<\"#buttons-1\"B><\"#buttons-2\"<\"#{$customId}.dt-buttons\">>><\"d-flex flex-fill flex-column flex-{$responsiveElement}-row\"l<\"ms-0 ms-{$responsiveElement}-auto\"f>>>";

    $availableExportButtons = ['copy', 'excel', 'pdf', 'csv', 'print'];

    $exportButtons = [];
    $customButtons = [];

    if (isset($datatableButtons) && is_array($datatableButtons) && !empty($datatableButtons)) {
        foreach($datatableButtons as $button) {
            if (is_string($button) && in_array($button, $availableExportButtons)) {
                $btn = [
                    'extend' => $button,
                    'footer' => true,
                ];
                if (in_array($button, ['pdf', 'excel', 'csv'])) $btn['download'] = 'open';

                $exportButtons[] = $btn;
            } elseif (is_array($button)) {
                if (array_key_exists('extend', $button)) {
                    if (in_array($button['extend'], $availableExportButtons)) {
                        if (in_array($button['extend'], ['pdf', 'excel', 'csv'])) $button['download'] = 'open';
                        
                        $exportButtons[] = $button;
                    }
                } else {
                    $customButtons[] = $button;
                }
            }
        }
    }

    $hasCustomButtons = !empty($customButtons);
    $needButtons = (!empty($exportButtons) || !empty($customButtons));

    $doms = [];

    if ($needButtons) {
        $doms[] = $hasCustomButtons ? $customButtonControlsDom : $buttonControlsDom;
    } else {
        $doms[] = $defaultControlDom;
    }

    $doms[] = $tableDom;
    $doms[] = $footerDom;

    $dtButtons = json_encode($exportButtons);
    $ctButtons = json_encode($customButtons);
    $dtDom = implode('', $doms);
@endphp

<style>
    div.dataTables_wrapper div.dataTables_processing {
        margin: 0;
        width: unset;
        height: unset;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
        padding: 0;
        border: none;
        overflow: hidden;
        background-color: rgba(var(--bs-white-rgb), 0.5);
        z-index: 1;
    }
</style>

<script>
    const datatableDom = '{!! $dtDom !!}';
    const datatablePagingType = 'full_numbers';
    const datatableLengMenu = [10, 25, 50];
    const datatableLanguange = {
        lengthMenu: "{{ __('datatable.lengthMenu') }}",
        zeroRecords: "{{ __('datatable.zeroRecords') }}",
        info: "{{ __('datatable.info') }}",
        infoEmpty: "{{ __('datatable.infoEmpty') }}",
        infoFiltered: "{{ __('datatable.infoFiltered') }}",
        search: "{{ __('datatable.search') }}",
        paginate: {
            first: "{!! __('datatable.paginate.first') !!}",
            previous: "{!! __('datatable.paginate.previous') !!}",
            next: "{!! __('datatable.paginate.next') !!}",
            last: "{!! __('datatable.paginate.last') !!}"
        }
    };
    const datatableButtons = {!! $dtButtons !!};
    const customButtons = {!! $ctButtons !!};
    const customId = '#{{ $customId }}';
    const spinerProcessing = '<div class="d-flex h-100 flex-column align-items-center justify-content-center"><div class="fa-beat-fade"><img src="{{ asset("images/favicon.png") }}" alt="" style="height:80px; width:auto;"></div><div class="fa-fade text-center">Processing...</div></div>';

    function customizeDatatable()
    {
        $('.dataTables_filter input').attr('placeholder', "{{ __('datatable.searchPlaceholder') }}").css({'max-width': '350px'});

        if (customButtons.length > 0) {
            $.each(customButtons, function(k, v) {
                const btn = $('<button class="dt-button buttons-html5"></button>');
                $.each(v, function($k, $v) {
                    if ($k == 'html'){
                        btn.html($v);
                    } else if ($k == 'class') {
                        btn.addClass($v);
                    } else {
                        btn.attr($k, $v);
                    }
                });
                $(customId + '.dt-buttons').append(btn);
            });

            if (datatableButtons.length > 0) {
                $('#buttons-1').addClass('me-2');
            }
        }

        $('.dataTables_processing').addClass('text-success').removeClass('card').html(spinerProcessing);
        const wrapper = '.dataTables_wrapper';
        const input = $(wrapper + ' .dataTables_filter input');
        const label = input.parent();
        label.parent().prepend(input);
        label.remove();
    }

    $(function() {
        $.fn.DataTable.ext.pager.numbers_length = 5;
    });
</script>
