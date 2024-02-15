<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NeoPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class ReferralLinkController extends Controller
{
    public function index(Request $request)
    {
        $referral = $request->mitraReferral;

        return view('mitra-referral', [
            'referral' => $referral
        ]);
    }

    public function register(Request $request)
    {
        $referral = $request->mitraReferral;
        $values = $request->except(['_token']);

        $domainLimits = implode(',', app('neo')->subDomainLimits());

        $uniqueUsername = new Unique('users', 'username');
        $uniqueEmail = new Unique('users', 'email');
        $uniqueIdentitas = new Unique('users', 'identity');
        $uniqueDomain = new Unique('users', 'sub_domain');

        $values['sub_domain'] = strtolower(str_replace([' ', '.', ','], ['-', '', ''], $values['market_name']));
        // $values['low_domain'] = strtolower($values['sub_domain']);
        $passwd = NeoPassword::min(6)->numbers()->letters();

        $validator = Validator::make($values, [
            'username' => ['required', 'string', 'min:6', 'max:30', 'regex:/^[\w_-]*$/', $uniqueUsername],
            'password' => ['required', 'confirmed', $passwd],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', $uniqueEmail],
            'identity' => ['required', 'string', 'max:50', $uniqueIdentitas],
            'phone' => ['required', 'digits_between:8,15', 'starts_with:08'],
            'market_name' => ['required', 'string', 'min:4', 'max:100', 'regex:/^[a-z0-9 _.,\-]+$/i'],
            // 'low_domain' => ['required', "not_in:{$domainLimits}"],
            'sub_domain' => ['required', 'string', 'min:4', 'max:100', "not_in:{$domainLimits}", $uniqueDomain],
        ], [
            'market_name.regex' => 'Format :attribute tidak dapat diterima.',
        ], [
            'username' => 'Username',
            'password' => 'Password',
            'name' => 'Nama Lengkap',
            'email' => 'Email',
            'identity' => 'No. Identitas',
            'phone' => 'No. Handphone',
            'market_name' => 'Nama Toko',
            'low_domain' => 'Nama Toko',
            'sub_domain' => 'Nama Toko',
        ]);

        if ($validator->fails()) {
            $pesan = '<div class="fw-bold mb-1">Registrasi gagal</div><ul class="mb-0 ps-3">';
            foreach ($validator->errors()->toArray() as $errors) {
                $pesan .= '<li>' . $errors[0] . '</li>';
            }
            $pesan .= '</ul>';

            return redirect()->back()->withInput()
                ->with('message', $pesan)
                ->with('messageClass', 'danger');
        }

        $timestamp = date('Y-m-d H:i:s');

        $values['password'] = Hash::make($values['password']);
        $values['activated'] = true;
        $values['activated_at'] = $timestamp;
        $values['is_login'] = true;
        $values['user_status'] = USER_STATUS_ACTIVE;
        $values['status_at'] = $timestamp;
        $values['referral_id'] = $values['upline_id'] = $referral->id;
        $values['user_group'] = USER_GROUP_MEMBER;
        $values['user_type'] = USER_TYPE_MITRA;
        $values['position_ext'] = USER_EXT_MTR;
        $values['branch_id'] = null;
        $values['mitra_type'] = MITRA_TYPE_RESELLER;

        DB::beginTransaction();
        try {
            $mitra = User::create($values);

            // TODO:
            // apakah ada emailan

            DB::commit();

            return redirect()->route('login')
                ->with('message', 'Registrasi sebagai mitra berhasil.')
                ->with('messageClass', 'success');
        } catch (\Exception $e) {
            DB::rollBack();

            $err = isLive() ? '' : ' ' . $e->getMessage();

            return redirect()->back()->withInput()
                ->with('message', 'Telah terjadi kesalahan pada server. Silahkan coba lagi.' . $err)
                ->with('messageClass', 'danger');
        }
    }
}
