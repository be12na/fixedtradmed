<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\RegisterNotification;
use App\Rules\NeoPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

class RegisterMitraController extends Controller
{
    public function create(Request $request)
    {
        $referral = $request->mitraReferral;
        $postUrl = route('regMitra.store', ['mitraReferral' => optional($referral)->username]);

        return view('register', [
            'referral' => $referral,
            'postUrl' => $postUrl,
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        // $values['mitra_type_reg'] = $values['mitra_type'];
        $domainLimits = implode(',', app('neo')->subDomainLimits());

        $uniqueUsername = new Unique('users', 'username');
        $uniqueEmail = new Unique('users', 'email');
        $uniqueIdentitas = new Unique('users', 'identity');
        $uniqueDomain = new Unique('users', 'sub_domain');

        $values['sub_domain'] = strtolower(str_replace([' ', '.', ','], ['-', '', ''], $values['market_name']));
        $passwd = NeoPassword::min(6)->numbers()->letters();

        $referralRules = [];

        if (array_key_exists('referral_id', $values)) {
            $exists = (new Exists('users', 'id'))
                ->where('user_group', USER_GROUP_MEMBER)
                ->where('user_type', USER_TYPE_MITRA)
                ->where('user_status', USER_STATUS_ACTIVE);

            $referralRules['referral_id'] = ['required', $exists];
        } else {
            $exists = (new Exists('users', 'username'))
                ->where('user_group', USER_GROUP_MEMBER)
                ->where('user_type', USER_TYPE_MITRA)
                ->where('user_status', USER_STATUS_ACTIVE)
                ->where('activated', true);

            $referralRules['referral_username'] = ['required', $exists];

            $referral = User::query()
                ->byMitraGroup()
                ->byStatus(USER_STATUS_ACTIVE)
                ->byActivated(true)
                ->byUsername($values['referral_username'] ?? '000')
                ->first();

            $values['referral_id'] = $referral ? $referral->id : null;
        }

        // 'identity' => ['required', 'string', 'max:50', $uniqueIdentitas],

        $validator = Validator::make($values, array_merge([
            'username' => ['required', 'string', 'min:6', 'max:30', 'regex:/^[\w_-]*$/', $uniqueUsername],
            'password' => ['required', 'confirmed', $passwd],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', $uniqueEmail],
            
            'phone' => ['required', 'digits_between:8,15', 'starts_with:08'],
            'market_name' => ['required', 'string', 'min:4', 'max:100', 'regex:/^[a-z0-9 _.,\-]+$/i'],
            'sub_domain' => ['required', 'string', 'min:4', 'max:100', "not_in:{$domainLimits}", $uniqueDomain],
        ], $referralRules), [
            'referral_username.exists' => ':attribute tidak terdaftar.',
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
            'referral_id' => 'Referral',
            'referral_username' => 'Referral',
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

        $dateTime = Carbon::now();

        $values['password'] = Hash::make($values['password']);
        $values['activated'] = true;
        $values['activated_at'] = $dateTime;
        $values['is_login'] = true;
        // $values['user_status'] = USER_STATUS_INACTIVE;
        $values['user_status'] = USER_STATUS_ACTIVE;
        $values['status_at'] = $dateTime;
        $values['upline_id'] = $values['referral_id'];
        $values['user_group'] = USER_GROUP_MEMBER;
        $values['user_type'] = USER_TYPE_MITRA;
        $values['position_ext'] = USER_EXT_MTR;
        $values['branch_id'] = null;
        $values['mitra_type_reg'] = MITRA_TYPE_DROPSHIPPER;
        $values['mitra_type'] = MITRA_TYPE_DROPSHIPPER;
        $values['level_id'] = MITRA_TYPE_DROPSHIPPER;

        // $price = Arr::get(MITRA_PRICES, $values['mitra_type'], 0);
        // // paket gratis = tdk ada transfer = tdk ada unik digit
        // $digit = ($price > 0) ? mt_rand(105, 148) : 0;
        // $total = $price + $digit;

        // $userPackageValues = [
        //     'code' => UserPackage::makeCode($values['mitra_type']),
        //     'package_id' => $values['mitra_type'],
        //     'price' => $price,
        //     'digit' => $digit,
        //     'total_price' => $total,
        // ];

        // jika gratis, jadikan status sudah transfer. sehingga tinggal diaktivasi oleh admin
        // if ($total == 0) {
        //     $userPackageValues['status'] = MITRA_PKG_TRANSFERRED;
        //     $userPackageValues['transfer_at'] = date('Y-m-d H:i:s');
        // }

        DB::beginTransaction();
        try {
            $mitra = User::create($values);
            // $mitra->userPackage()->create($userPackageValues);

            $mitra->notify(new RegisterNotification($mitra, 'database', ['driver' => 'mail']));
            $mitra->notify(new RegisterNotification($mitra, 'database', ['driver' => 'onesender']));

            DB::commit();

            return redirect()->route('login')
                ->with('message', 'Registrasi sebagai member berhasil.')
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
