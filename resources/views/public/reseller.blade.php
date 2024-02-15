<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | Reseller</title>
    <link rel="icon" sizes="32x32" href="{{ asset('images/favicon-32.png') }}" type="image/png">
    <link rel="icon" sizes="16x16" href="{{ asset('images/favicon-16.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ asset('images/favicon-32.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/DataTables-1.11.4/css/dataTables.bootstrap5.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/datatables/Buttons-2.2.2/css/buttons.dataTables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('css/app.css?t=' . mt_rand(1000001, 9999999)) }}"> --}}
    <link rel="stylesheet" href="{{ asset('css/style.css?t=' . mt_rand(1000001, 9999999)) }}">
    <link rel="stylesheet" href="{{ asset('css/reseller.css?t=' . mt_rand(1000001, 9999999)) }}">
</head>
<body class="bg-light">
    <div class="container-fluid container-lg py-3 py-lg-5">
        <div class="row g-3">
            <div class="col-xl-4">
                <form class="box-rounded p-2 p-lg-3 bg-success text-dark shadow-sm" method="GET" action="{{ route('reseller.index') }}" style="--bs-bg-opacity:0.125;">
                    <a class="d-flex align-items-end text-decoration-none link-secondary" href="{{ route('home') }}">
                        <img src="{{ asset('images/logo-main.png') }}" style="max-height:60px; filter:brightness(80%) contrast(50%);">
                        <div>
                            <div class="fs-3 fw-bold">{{ config('app.name') }}</div>
                            <div class="lh-1 small">Daftar Reseller</div>
                        </div>
                    </a>
                    <hr/>
                    <div class="mb-3">
                        <label class="d-block required">Propinsi</label>
                        <select class="form-select select2bs4 select2-custom select-region" name="prov" id="province-id" autocomplete="off" data-select-url="{{ route('selectProvince') }}" data-change-sub="#city-id" data-placeholder="Propinsi">
                            @if (!empty($province))
                                <option value="{{ $province->id }}">
                                    {{ $province->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="d-block required">Kota/Kabupaten</label>
                        <select class="form-select select2bs4 select2-custom select-region" name="city" id="city-id" autocomplete="off" data-select-url="{{ route('selectCity') }}" data-parent-select="#province-id" data-placeholder="Kota/Kabupaten">
                            @if (!empty($city))
                                <option value="{{ $city->id }}">
                                    {{ $city->name }}
                                </option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="search" id="search" value="{{ $search }}" placeholder="Cari Reseller" autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fa fa-search me-2"></i>Cari
                    </button>
                </form>
            </div>
            <div class="col-xl-8">
                <table class="table table-borderless w-100" id="table">
                    <thead>
                        <tr>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody class="fs-auto"></tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/datatables.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables/DataTables-1.11.4/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/js/i18n/id.js') }}"></script>

    <script>
        $(function() {
            $(document).on('select2:open', '.select2bs4.select2-custom', function(e) {
                const x = $('.select2-container.select2-container--open .select2-search__field');
                if (x.length) {
                    x[0].focus();
                }
            });

            $('.select-region').each(function(k, obj) {
                const select = $(obj);
                const placeholder = select.data('placeholder');
                const url = select.data('select-url');
                const changeSub = select.data('change-sub');
                const baseParent = select.data('parent-select');

                const selectRegion = select.select2({
                    theme: 'classic',
                    placeholder: '-- ' + placeholder + ' --',
                    allowClear: true,
                    ajax: {
                        url: function(params) {
                            params.current = select.val();

                            if (baseParent) {
                                params.parent = $(baseParent).val();
                            }
                            
                            return url;
                        },
                        data: function (params) {
                            let dt = {
                                search: params.term,
                                current: params.current
                            };

                            if (baseParent) {
                                dt.parent = params.parent;
                            }

                            return dt;
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        }
                    }
                });

                if (changeSub) {
                    const targetSub = $(changeSub);

                    selectRegion.on('select2:select', function (e) {
                        targetSub.empty().trigger({type: 'select2:clear'});
                    }).on('select2:clear', function(e) {
                        targetSub.empty().trigger({type: 'select2:clear'});
                    })
                }
            });

            table = $('#table').DataTable({
                dom: 'lfrtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("reseller.dataTable") }}',
                    data: function(d) {
                        d.prov = '{{ $province ? $province->id : "" }}';
                        d.city = '{{ $city ? $city->id : "" }}';
                        d.src = '{{ $search }}';
                    }
                },
                info: false,
                lengthChange: false,
                searching: false,
                sort: false,
                language: {
                    zeroRecords: " ",
                    paginate: {
                        first: "{!! __('datatable.paginate.first') !!}",
                        previous: "{!! __('datatable.paginate.previous') !!}",
                        next: "{!! __('datatable.paginate.next') !!}",
                        last: "{!! __('datatable.paginate.last') !!}"
                    }
                },
                columns: [
                    {data: 'reseller'},
                ],
            });

            $('div.dataTables_wrapper').addClass('box-rounded shadow-sm p-2 p-lg-3 bg-dark').css({'--bs-bg-opacity':'0.025'});
        });
    </script>
</body>
</html>