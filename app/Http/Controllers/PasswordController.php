<?php

namespace App\Http\Controllers;

use App\Rules\NeoPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function passwordForm(Request $request)
    {
        return view('password', [
            'windowTitle' => 'Password',
            'breadcrumbs' => ['Password']
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = $request->user();
        $inputOldPassword = $request->get('old_password');

        $responCode = 200;
        $responText = route('dashboard');

        if (!Hash::check($inputOldPassword, $user->password)) {
            $responCode = 400;
            $responText = view('partials.alert', [
                'message' => 'Password lama yang anda masukkan salah.',
                'messageClass' => 'danger'
            ])->render();
        } else {
            $values = $request->except(['_token', 'old_password']);
            $passwd = NeoPassword::min(6)->numbers()->letters();

            $validator = Validator::make($values, [
                'password' => ['required', 'confirmed', $passwd]
            ], [], [
                'password' => 'Password Baru'
            ]);

            if ($validator->fails()) {
                $responCode = 400;
                $responText = view('partials.alert', [
                    'message' => $validator->errors()->first(),
                    'messageClass' => 'danger'
                ])->render();
            } else {
                try {
                    $user->timestamps = true;
                    $user->update(['password' => Hash::make($values['password'])]);

                    session([
                        'message' => 'Password berhasil diubah.',
                        'messageClass' => 'success'
                    ]);
                } catch (\Exception $e) {
                    $responCode = 500;
                    $responText = view('partials.alert', [
                        'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi',
                        'messageClass' => 'danger'
                    ])->render();
                }
            }
        }

        return response($responText, $responCode);
    }
}
