<?php

namespace App\Http\Controllers\Main\Settings;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MitraController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    private function getAccessMenu(): Collection
    {
        $result = collect();

        if (hasPermission('main.settings.mitra.purchase.discount.index')) {
            $result->push((object) [
                'id' => 'mitraDiscount',
                'route' => 'main.settings.mitra.purchase.discount.index',
                'text' => 'Diskon Belanja'
            ]);
        }

        if (hasPermission('main.settings.mitra.purchase.cashback.index')) {
            $result->push((object) [
                'id' => 'mitraCashback',
                'route' => 'main.settings.mitra.purchase.cashback.index',
                'text' => 'Cashback Bulanan'
            ]);
        }

        return $result;
    }

    private function getView(string $activeMenu, $content, array $breadcrumbs = null)
    {
        $menus = $this->getAccessMenu();

        return view('main.settings.mitra.index', [
            'windowTitle' => 'Pengaturan Member',
            'breadcrumbs' => array_merge(['Pengaturan', 'Member'], $breadcrumbs),
            'activeMenu' => $activeMenu,
            'menus' => $menus,
            'content' => $content
        ]);
    }

    public function index(Request $request)
    {
        $route = $this->getAccessMenu()->first();
        if (empty($route)) return pageError('Anda tidak memiliki akses untuk membuka halaman tesebut.');

        return redirect()->route($route->route);
    }

    // discount
    public function indexDiscount(Request $request)
    {
        $settings = $this->neo->listSettingMitraDiscount();

        $content = view('main.settings.mitra.discount.index', [
            'settings' => $settings
        ])->render();

        return $this->getView('mitraDiscount', $content, ['Diskon Belanja']);
    }

    public function editDiscount(Request $request)
    {
        $mode = $request->get('mode');
        $data = null;

        if (in_array($mode, ['new', 'edit'])) {
            $id = $request->get('id');

            if ($mode == 'edit') {
                $data = $this->neo->settingMitraDiscountById($id);
            }
        }

        return view('main.settings.mitra.discount.form', [
            'setting' => $data,
            'mode' => $mode,
            'modalHeader' => (($mode == 'new') ? 'Tambah' : 'Ubah') . ' Diskon Belanja',
        ]);
    }

    public function updateDiscount(Request $request)
    {
        $mode = $request->get('mode');

        $responCode = 200;
        $responText = route("main.settings.mitra.purchase.discount.index");

        if (!in_array($mode, ['new', 'edit'])) {
            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => 'Proses tidak dikenali.',
                'messageClass' => 'danger'
            ])->render();
        } else {
            $id = $request->get('id');
            $isNew = ($mode == 'new');
            $data = null;
            $continue = true;

            if (!$isNew) {
                $data = $this->neo->settingMitraDiscountById($id);
                if (empty($data)) {
                    $responCode = 404;
                    $responText = view('partials.alert', [
                        'message' => 'Data tidak ditemukan.',
                        'messageClass' => 'danger'
                    ])->render();
                    $continue = false;
                }
            }

            if ($continue === true) {
                $values = $request->except(['_token', 'id', 'mode']);

                $validator = Validator::make($values, [
                    'min_purchase' => ['required', 'integer', 'min:0'],
                    'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
                ], [], [
                    'min_purchase' => 'Minimal Belanja',
                    'percent' => 'Diskon',
                ]);

                if (!$validator->fails()) {
                    $values['mitra_type'] = MITRA_TYPE_AGENT;
                    $values['set_by'] = $request->user()->id;

                    DB::beginTransaction();

                    try {
                        $this->neo->updateSettingMitraDiscount($values, $data);

                        session([
                            'message' => 'Diskon Belanja Member ' . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
                            'messageClass' => 'success'
                        ]);
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $responCode = 500;
                        $responText = view('partials.alert', [
                            'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi.' . (!isLive() ? $e->getMessage() : ''),
                            'messageClass' => 'danger'
                        ])->render();
                    }
                } else {
                    $responCode = 400;
                    $responText = $this->validationMessages($validator);
                }
            }
        }

        return response($responText, $responCode);
    }

    // cashback
    public function indexCashback(Request $request)
    {
        $settings = $this->neo->listSettingMitraCashback();

        $content = view('main.settings.mitra.cashback.index', [
            'settings' => $settings
        ])->render();

        return $this->getView('mitraCashback', $content, ['Cashback Bulanan']);
    }

    public function editCashback(Request $request)
    {
        $mode = $request->get('mode');
        $data = null;

        if (in_array($mode, ['new', 'edit'])) {
            $id = $request->get('id');

            if ($mode == 'edit') {
                $data = $this->neo->SettingMitraCashbackById($id);
            }
        }

        return view('main.settings.mitra.cashback.form', [
            'setting' => $data,
            'mode' => $mode,
            'modalHeader' => (($mode == 'new') ? 'Tambah' : 'Ubah') . ' Cashback Bulanan',
        ]);
    }

    public function updateCashback(Request $request)
    {
        $mode = $request->get('mode');

        $responCode = 200;
        $responText = route("main.settings.mitra.purchase.cashback.index");

        if (!in_array($mode, ['new', 'edit'])) {
            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => 'Proses tidak dikenali.',
                'messageClass' => 'danger'
            ])->render();
        } else {
            $id = $request->get('id');
            $isNew = ($mode == 'new');
            $data = null;
            $continue = true;

            if (!$isNew) {
                $data = $this->neo->SettingMitraCashbackById($id);
                if (empty($data)) {
                    $responCode = 404;
                    $responText = view('partials.alert', [
                        'message' => 'Data tidak ditemukan.',
                        'messageClass' => 'danger'
                    ])->render();
                    $continue = false;
                }
            }

            if ($continue === true) {
                $values = $request->except(['_token', 'id', 'mode']);

                $validator = Validator::make($values, [
                    'min_purchase' => ['required', 'integer', 'min:0'],
                    'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
                ], [], [
                    'min_purchase' => 'Min. Belanja Bulanan',
                    'percent' => 'Cashback',
                ]);

                if (!$validator->fails()) {
                    $values['mitra_type'] = MITRA_TYPE_AGENT;
                    $values['set_by'] = $request->user()->id;

                    DB::beginTransaction();

                    try {
                        $this->neo->updateSettingMitraCashback($values, $data);

                        session([
                            'message' => 'Cashback Bulanan Member ' . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
                            'messageClass' => 'success'
                        ]);
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $responCode = 500;
                        $responText = view('partials.alert', [
                            'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi.' . (!isLive() ? $e->getMessage() : ''),
                            'messageClass' => 'danger'
                        ])->render();
                    }
                } else {
                    $responCode = 400;
                    $responText = $this->validationMessages($validator);
                }
            }
        }

        return response($responText, $responCode);
    }
}
