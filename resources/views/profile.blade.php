@php
    $user = isset($user) ? $user : Auth::user();
    $isEdit = isset($isEdit) ? $isEdit : false;
    $breadCrumbItems = ['Profile'];
    if ($isEdit === true) {
        $breadCrumbItems[] = 'Edit';
    }
@endphp

@extends($user->layout_blade)

@section('content')
<div class="d-block" id="alert-container">
    @include('partials.alert')
</div>

@if ($isEdit === true)
    <form class="card mb-3 fs-auto" method="POST" action="{{ route('profile.update') }}" data-alert-container="#alert-container">
        <div class="card-body">
            @csrf
            <div class="row g-2">
                <div class="col-md-6">
                    <label class="d-block required">No. Identitas</label>
                    <input type="text" class="form-control" name="identity" value="{{ $user->identity }}" autocomplete="off" required autofocus>
                </div>
                <div class="col-md-6">
                    <label class="d-block required">Nama Lengkap</label>
                    <input type="text" class="form-control" name="name" value="{{ $user->name }}" autocomplete="off" required>
                </div>
                <div class="col-12">
                    <label class="d-block required">Alamat</label>
                    <input type="text" class="form-control" name="address" value="{{ $user->address }}" autocomplete="off" required>
                </div>
                <div class="col-sm-6">
                    <label class="d-block required">Propinsi</label>
                    <select class="form-select select2bs4 select2-custom select-region" name="province_id" id="province-id" autocomplete="off" data-select-url="{{ route('selectProvince') }}" data-change-sub="#city-id" data-placeholder="Propinsi" required>
                        @if (!empty($user->province_id))
                            <option value="{{ $user->province_id }}">
                                {{ $user->province }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="d-block required">Kota/Kabupaten</label>
                    <select class="form-select select2bs4 select2-custom select-region" name="city_id" id="city-id" autocomplete="off" data-select-url="{{ route('selectCity') }}" data-parent-select="#province-id" data-change-sub="#district-id" data-placeholder="Kota/Kabupaten" required>
                        @if (!empty($user->city_id))
                            <option value="{{ $user->city_id }}">
                                {{ $user->city }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="d-block required">Kecamatan</label>
                    <select class="form-select select2bs4 select2-custom select-region" name="district_id" id="district-id" autocomplete="off" data-select-url="{{ route('selectDistrict') }}" data-parent-select="#city-id" data-change-sub="#village-id" data-placeholder="Kecamatan" required>
                        @if (!empty($user->district_id))
                            <option value="{{ $user->district_id }}">
                                {{ $user->district }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="d-block required">Desa/Kelurahan</label>
                    <select class="form-select select2bs4 select2-custom select-region" name="village_id" id="village-id" autocomplete="off" data-select-url="{{ route('selectVillage') }}" data-parent-select="#district-id" data-placeholder="Desa/Kelurahan" required>
                        @if (!empty($user->village_id))
                            <option value="{{ $user->village_id }}">
                                {{ $user->village }}
                            </option>
                        @endif
                    </select>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <label class="d-block">Kode Pos</label>
                    <input type="text" name="pos_code" class="form-control" value="{{ $user->pos_code }}" placeholder="Kode Pos" autocomplete="off">
                </div>
                <div class="col-sm-6 col-md-8 col-lg-4">
                    <label class="d-block required">Whatsapp</label>
                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" placeholder="Whatsapp" autocomplete="off" required>
                </div>
                <div class="col-lg-6">
                    <label class="d-block required">Email</label>
                    <input type="text" name="email" class="form-control" value="{{ $user->email }}" placeholder="Email" autocomplete="off" required>
                </div>
                <div class="col-lg-6">
                    <label class="d-block">Facebook</label>
                    <input type="text" name="facebook" class="form-control" value="{{ $user->facebook }}" placeholder="Username Facebook" autocomplete="off">
                </div>
                <div class="col-lg-6">
                    <label class="d-block">Tokopedia</label>
                    <input type="text" name="tokopedia" class="form-control" value="{{ $user->tokopedia }}" placeholder="Username Tokopedia" autocomplete="off">
                </div>
                <div class="col-lg-6">
                    <label class="d-block">Tiktok</label>
                    <input type="text" name="tiktok" class="form-control" value="{{ $user->tiktok }}" placeholder="Username Tiktok" autocomplete="off">
                </div>
                <div class="col-lg-6">
                    <label class="d-block">Instagram</label>
                    <input type="text" name="instagram" class="form-control" value="{{ $user->instagram }}" placeholder="Username Instagram" autocomplete="off">
                </div>
                <div class="col-lg-6">
                    <label class="d-block">Shopee</label>
                    <input type="text" name="shopee" class="form-control" value="{{ $user->shopee }}" placeholder="Username Shopee" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-save me-1"></i>
                Simpan
            </button>
            @if ($user->is_profile)
                <button type="button" class="btn btn-sm btn-warning" onclick="window.location.replace('{{ route('profile.index') }}')">
                    <i class="fa-solid fa-undo me-1"></i>
                    Batal
                </button>
            @endif
        </div>
    </form>
@else
    <div class="card fs-auto">
        <div class="card-header">
            <button type="button" class="btn btn-sm btn-primary" onclick="window.location.replace('{{ route('profile.edit') }}')">
                <i class="fa-solid fa-pencil me-1"></i>Edit
            </button>
        </div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-xl-6">
                    <div class="row g-2 row-cols-1">
                        <div class="col">
                            <div class="fw-bold">No. Identitas:</div>
                            <div>{{ $user->identity }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Nama Lengkap:</div>
                            <div>{{ $user->name }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Alamat:</div>
                            <div>{{ $user->complete_address }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">No. Handphone:</div>
                            <div>{{ $user->phone }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Email:</div>
                            <div>{{ $user->email }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="row g-2 row-cols-1">
                        <div class="col">
                            <div class="fw-bold">Facebook:</div>
                            <div>{{ $user->socialMediaUrl('facebook', '-') }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Tokopedia:</div>
                            <div>{{ $user->socialMediaUrl('tokopedia', '-') }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Tiktok:</div>
                            <div>{{ $user->socialMediaUrl('tiktok', '-') }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Instagram:</div>
                            <div>{{ $user->socialMediaUrl('instagram', '-') }}</div>
                        </div>
                        <div class="col">
                            <div class="fw-bold">Shopee:</div>
                            <div>{{ $user->socialMediaUrl('shopee', '-') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@if ($isEdit === true)
@push('vendorCSS')
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
@endpush

@push('vendorJS')
<script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ asset('vendor/select2/js/i18n/id.js') }}"></script>
@endpush

@push('scripts')
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
    });
</script>
@endpush
@endif