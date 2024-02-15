<?php

namespace App\Http\Controllers;

use App\Repositories\RegionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->is_profile) {
            $this->keepMessage();

            return redirect()->route('profile.edit');
        }

        return view('profile', [
            'windowTitle' => 'Profile',
            'breadcrumbs' => ['Profile']
        ]);
    }

    public function edit(Request $request)
    {
        return view('profile', [
            'windowTitle' => 'Profile',
            'breadcrumbs' => ['Profile', 'Edit'],
            'isEdit' => true,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $values = $request->except(['_token']);

        $validator = Validator::make($values, [
            'identity' => ['required', 'string', 'max:50', "unique:users,identity,{$user->id},id"],
            'name' => ['required', 'string', 'max:100'],
            'address' => 'required|string|max:250',
            'province_id' => 'required|exists:provinces,id',
            'city_id' => 'required|exists:regencies,id',
            'district_id' => 'required|exists:districts,id',
            'village_id' => 'required|exists:villages,id',
            'pos_code' => 'nullable|digits:5',
            'email' => ['required', 'string', 'max:100', 'email'],
            'phone' => ['required', 'digits_between:8,15', 'starts_with:08'],
            'facebook' => ['nullable', 'string', 'max:100'],
            'tokopedia' => ['nullable', 'string', 'max:100'],
            'tiktok' => ['nullable', 'string', 'max:100'],
            'instagram' => ['nullable', 'string', 'max:100'],
            'shopee' => ['nullable', 'string', 'max:100'],
        ], [], [
            'identity' => 'No. Identitas',
            'name' => 'Nama',
            'address' => 'Alamat',
            'province_id' => 'Propinsi',
            'city_id' => 'Kota/Kabupaten',
            'district_id' => 'Kecamatan',
            'village_id' => 'Desa/Kelurahan',
            'pos_code' => 'Kode Pos',
            'email' => 'Email',
            'phone' => 'No. Handphone',
        ]);

        $responCode = 200;
        $responText = route('profile.index');

        if ($validator->fails()) {
            $responCode = 400;
            $responText = $this->validationMessages($validator);
        } else {
            $okRegion = true;
            $village = RegionRepository::getVillageById($values['village_id'], ['district', 'city', 'province']);

            if (empty($village)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Desa / Kelurahan tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $district = $okRegion ? $village->district : null;
            if (empty($district)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Kecamatan tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $city = $okRegion ? $district->city : null;
            if (empty($city)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Kota / Kabupaten tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $province = $okRegion ? $city->province : null;
            if (empty($city)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Propinsi tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            if ($okRegion === true) {
                $values['province_id'] = $province->id;
                $values['province'] = $province->name;
                $values['city_id'] = $city->id;
                $values['city'] = $city->name;
                $values['district_id'] = $district->id;
                $values['district'] = $district->name;
                $values['village_id'] = $village->id;
                $values['village'] = $village->name;
                $values['is_profile'] = true;
                $values['profile_at'] = date('Y-m-d H:i:s');

                try {
                    $user->update($values);

                    session([
                        'message' => "Profile berhasil diupdate.",
                        'messageClass' => 'success'
                    ]);
                } catch (\Exception $e) {
                    $moreMessage = isLive() ? '' : $e->getMessage();

                    $responCode = 500;
                    $responText = view('partials.alert', [
                        'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                        'messageClass' => 'danger'
                    ])->render();
                }
            }

            return response($responText, $responCode);
        }
    }
}
