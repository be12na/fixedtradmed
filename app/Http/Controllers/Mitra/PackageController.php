<?php

namespace App\Http\Controllers\Mitra;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function activationTransfer(Request $request)
    {
        $user = $request->user();
        $userPackage = $user->userPackage;

        if (!$request->isMethod('POST')) {
            $banks = collect();
            $showBanks = true;

            if ($userPackage->total_price > 0) {
                $mainBanks = $this->neo->mainBanks(true);
                foreach ($mainBanks as $mainBank) {
                    $banks->push((object) [
                        'id' => $mainBank->id,
                        'bank_code' => $mainBank->bank_code,
                        'bank_name' => $mainBank->bank_name,
                        'account_name' => $mainBank->account_name,
                        'account_no' => $mainBank->account_no,
                        'upload' => 1,
                    ]);
                }
                $banks = $banks->groupBy('bank_name');
            } else {
                $showBanks = false;
            }

            return view('mitra.package.transfer', [
                'userPackage' => $userPackage,
                'windowTitle' => 'Aktifasi Paket',
                'breadcrumbs' => ['Paket', 'Aktifasi'],
                'banks' => $banks,
                'showBanks' => $showBanks,
                'postUrl' => route('mitra.package.transfer'),
            ]);
        }

        $values = $request->only(['bank_id']);
        $mainBanks = $this->neo->mainBanks(true);
        $mainBankIds = implode(',', $mainBanks->pluck('id')->toArray());

        $validator = Validator::make($values, [
            'bank_id' => ['required', "in:{$mainBankIds}"],
        ], [
            'bank_id.required' => ':attribute harus dipilih.',
        ], [
            'bank_id' => 'Rekening Bank'
        ]);

        $responCode = 200;
        $responText = route('mitra.package.index');

        if ($validator->fails()) {
            $pesan = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
            foreach ($validator->errors()->toArray() as $errors) {
                $pesan .= '<li>' . $errors[0] . '</li>';
            }
            $pesan .= '</ul>';

            $responCode = 400;
            $responText = view('partials.alert', [
                'message' => $pesan,
                'messageClass' => 'danger'
            ])->render();
        } else {
            $bank = $mainBanks->where('id', '=', $values['bank_id'])->first();
            $values = array_merge($values, [
                'bank_code' => $bank->bank_code,
                'bank_name' => $bank->bank_name,
                'account_no' => $bank->account_no,
                'account_name' => $bank->account_name,
                'status' => MITRA_PKG_TRANSFERRED,
                'transfer_at' => date('Y-m-d H:i:s'),
            ]);

            try {
                $userPackage->update($values);

                session([
                    'message' => 'Transfer Berhasil.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }

    public function repeatOrder(Request $request)
    {
        $routeName = $request->route()->getName();
        $userPackageRO = $request->userPackageRO;

        if (!$request->isMethod('POST')) {
            if ($routeName == 'mitra.package.ro.index') {
                return view('mitra.package.repeat-order');
            }

            $banks = collect();
            $showBanks = true;

            $mainBanks = $this->neo->mainBanks(true);
            foreach ($mainBanks as $mainBank) {
                $banks->push((object) [
                    'id' => $mainBank->id,
                    'bank_code' => $mainBank->bank_code,
                    'bank_name' => $mainBank->bank_name,
                    'account_name' => $mainBank->account_name,
                    'account_no' => $mainBank->account_no,
                    'upload' => 1,
                ]);
            }
            $banks = $banks->groupBy('bank_name');

            return view('mitra.package.transfer', [
                'userPackage' => $userPackageRO,
                'windowTitle' => 'Repeat Order',
                'breadcrumbs' => ['Paket', 'Repeat Order'],
                'banks' => $banks,
                'showBanks' => $showBanks,
                'postUrl' => route('mitra.package.ro.saveTransfer', ['userPackageRO' => $userPackageRO->id]),
            ]);
        }

        $user = $request->user();
        $responCode = 200;
        $responText = route('dashboard');

        if ($routeName == 'mitra.package.ro.store') {
            $userPackage = $user->userPackage;
            $packageId = $userPackage->package_id;
            $price = Arr::get(MITRA_PRICES, $packageId, 0);
            $digit = mt_rand(105, 148);
            $total = $price + $digit;

            try {
                $ro = $user->repeatOrders()->create([
                    'code' => UserPackage::makeCode($packageId),
                    'package_id' => $packageId,
                    'price' => $price,
                    'digit' => $digit,
                    'total_price' => $total,
                    'type' => TRANS_PKG_REPEAT_ORDER,
                ]);

                $responText = route('mitra.package.ro.transfer', ['userPackageRO' => $ro->id]);

                session([
                    'message' => 'Transaksi Repeat Order berhasil.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi.<br/>{$moreMessage}",
                    'messageClass' => 'danger'
                ])->render();
            }
        } elseif ($routeName == 'mitra.package.ro.saveTransfer') {
            $values = $request->only(['bank_id']);
            $mainBanks = $this->neo->mainBanks(true);
            $mainBankIds = implode(',', $mainBanks->pluck('id')->toArray());

            $validator = Validator::make($values, [
                'bank_id' => ['required', "in:{$mainBankIds}"],
            ], [
                'bank_id.required' => ':attribute harus dipilih.',
            ], [
                'bank_id' => 'Rekening Bank'
            ]);

            $responCode = 200;
            $responText = route('mitra.package.index');

            if ($validator->fails()) {
                $pesan = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
                foreach ($validator->errors()->toArray() as $errors) {
                    $pesan .= '<li>' . $errors[0] . '</li>';
                }
                $pesan .= '</ul>';

                $responCode = 400;
                $responText = view('partials.alert', [
                    'message' => $pesan,
                    'messageClass' => 'danger'
                ])->render();
            } else {
                $bank = $mainBanks->where('id', '=', $values['bank_id'])->first();
                $values = array_merge($values, [
                    'bank_code' => $bank->bank_code,
                    'bank_name' => $bank->bank_name,
                    'account_no' => $bank->account_no,
                    'account_name' => $bank->account_name,
                    'status' => MITRA_PKG_TRANSFERRED,
                    'transfer_at' => date('Y-m-d H:i:s'),
                ]);

                try {
                    $userPackageRO->update($values);

                    session([
                        'message' => 'Transfer Berhasil.',
                        'messageClass' => 'success'
                    ]);

                    $responText = route('mitra.package.history');
                } catch (\Exception $e) {
                    $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

                    $responCode = 500;
                    $responText = view('partials.alert', [
                        'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi.<br/>{$moreMessage}",
                        'messageClass' => 'danger'
                    ])->render();
                }
            }
        }

        return response($responText, $responCode);
    }

    public function history(Request $request)
    {
        return view('mitra.package.history', [
            'userPackages' => $request->user()->packageTransactions,
            'windowTitle' => 'Paket History',
            'breadcrumbs' => ['Paket', 'History'],
        ]);
    }
}
