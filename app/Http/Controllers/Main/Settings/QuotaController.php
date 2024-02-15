<?php

namespace App\Http\Controllers\Main\Settings;

use App\Http\Controllers\Controller;
use App\Models\PurchaseQuota;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class QuotaController extends Controller
{
    public function index(Request $request)
    {
        $rows = collect();
        $purchaseQuotas = PurchaseQuota::query()->orderBy('package_id')->get();

        foreach (PRODUCT_PURCHASES as $key => $name) {
            $row = $purchaseQuotas->where('package_id', '=', $key)->first();

            $rows->push((object) [
                'package_id' => $key,
                'name' => $name,
                'quota' => $row ? $row->quota : 0,
                'point' => $row ? $row->point : 0,
            ]);
        }

        return view('main.settings.quota.index', [
            'rows' => $rows,
            'windowTitle' => 'Pengaturan Kuota Belanja',
            'breadcrumbs' => ['Pengaturan', 'Kuota Belanja']
        ]);
    }

    public function edit(Request $request)
    {
        $quotaPackageId = $request->quotaPackage;
        $purchaseQuota = PurchaseQuota::query()->byPackage($quotaPackageId)->first();
        $quota = $purchaseQuota ? $purchaseQuota->quota : 0;
        $point = $purchaseQuota ? $purchaseQuota->point : 0;
        $packageName = Arr::get(PRODUCT_PURCHASES, $quotaPackageId);

        if (!$request->isMethod('POST')) {
            return view('main.settings.quota.form', [
                'quotaPackageId' => $quotaPackageId,
                'packageName' => $packageName,
                'quota' => $quota,
                'point' => $point,
            ]);
        }

        $validator = Validator::make($values = $request->only(['package_id', 'quota', 'point']), [
            'quota' => ['required', 'integer', 'min:0'],
            'point' => ['required', 'integer', 'min:0'],
        ], [], [
            'quota' => 'Kuota Belanja',
            'point' => 'Point Belanja',
        ]);

        $responCode = 200;
        $responText = route('main.settings.quota.index');

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
            DB::beginTransaction();

            try {
                if (!empty($purchaseQuota)) {
                    $purchaseQuota->delete();
                }

                PurchaseQuota::create($values);

                session([
                    'message' => "Kuota Belanja Paket <b>{$packageName}</b> berhasil diubah.",
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi ' . $e->getMessage(),
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }
}
