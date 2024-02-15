<?php

namespace App\Http\Controllers\Main\Settings;

use App\Helpers\AppStructure;
use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class BonusController extends Controller
{
    private Neo $neo;
    private AppStructure $appStructure;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->appStructure = app('appStructure');
    }

    private function getAccessMenu(): Collection
    {
        $result = collect();

        if (hasPermission('main.settings.bonus.royalty.index')) {
            $result->push((object) [
                'id' => 'bonusRoyalty',
                'route' => route('main.settings.bonus.royalty.index'),
                'text' => 'Bonus Royalty',
                'type' => 'menu',
            ]);
        }

        if (hasPermission('main.settings.bonus.override.index')) {
            $result->push((object) [
                'id' => 'bonusOverride',
                'route' => route('main.settings.bonus.override.index'),
                'text' => 'Bonus Override',
                'type' => 'menu',
            ]);
        }

        if (hasPermission('main.settings.bonus.team.index')) {
            $result->push((object) [
                'id' => 'bonusTeam',
                'route' => route('main.settings.bonus.team.index'),
                'text' => 'Bonus Team',
                'type' => 'menu',
            ]);
        }

        if (hasPermission('main.settings.bonus.sell.index')) {
            $result->push((object) [
                'id' => 'bonusSell',
                'route' => route('main.settings.bonus.sell.index'),
                'text' => 'Bonus Penjualan',
                'type' => 'menu',
            ]);
        }

        if (hasPermission('main.settings.bonus.mitraPremiumShopping.index')) {
            $result->push((object) [
                'id' => null,
                'route' => null,
                'text' => null,
                'type' => 'separator',
            ]);

            foreach (BONUS_TYPE_MITRA_SHOPPINGS as $key => $arrValues) {
                $result->push((object) [
                    'id' => $arrValues['pageKey'],
                    'route' => route('main.settings.bonus.mitraPremiumShopping.index', ['settingBonusMitraShoppingTarget' => $arrValues['routeKey']]),
                    'text' => 'Bonus ' . $arrValues['title'],
                    'type' => 'menu',
                ]);
            }
        }

        return $result;
    }

    private function getView(string $activeMenu, $content, array $breadcrumbs = null)
    {
        $menus = $this->getAccessMenu();

        return view('main.settings.bonus.index', [
            'windowTitle' => 'Pengaturan Bonus',
            'breadcrumbs' => array_merge(['Pengaturan', 'Bonus'], $breadcrumbs),
            'activeMenu' => $activeMenu,
            'menus' => $menus,
            'content' => $content
        ]);
    }

    private function getAvailablePositions(array $usedPositionIds, bool $isInternal, $ignoreId = null): Collection
    {
        $structure = collect();
        // $positions = $this->appStructure->getAllPositions($isInternal);
        $positions = $this->appStructure->getAllPositions(true);

        if (!$isInternal) {
            $positions = $positions->reject(function ($value, $key) {
                // return ($value->id == USER_EXT_MTR);
                return ($value->id > USER_INT_MGR);
            });
        }

        foreach ($positions as $row) {
            if ((!is_null($ignoreId) && ($row->id == $ignoreId)) || !in_array($row->id, $usedPositionIds)) $structure->push($row);
        }

        return $structure;
    }

    public function index(Request $request)
    {
        $route = $this->getAccessMenu()->first();
        if (empty($route)) return pageError('Anda tidak memiliki akses untuk membuka halaman tesebut.');

        return redirect()->to($route->route);
    }

    // BONUS ROYALTY
    public function indexRoyalty(Request $request)
    {
        $data = (object) [
            'internal' => $this->neo->listSettingRoyalty(true),
            'external' => $this->neo->listSettingRoyalty(false),
        ];

        $content = view('main.settings.bonus.royalty.index', [
            'data' => $data
        ])->render();

        return $this->getView('bonusRoyalty', $content, ['Royalty']);
    }

    public function editRoyalty(Request $request)
    {
        $category = $request->get('category');
        $mode = $request->get('mode');
        $data = null;
        $title = '';
        $positions = collect();

        if (in_array($mode, ['new', 'edit']) && in_array($category, ['internal', 'external'])) {
            $id = $request->get('id');
            $isInternal = ($category === 'internal');
            $title = $isInternal ? 'Internal' : 'Eksternal';
            $exists = $this->neo->listSettingRoyalty($isInternal);
            $positionIds = $exists->pluck('position_id')->toArray();

            if ($mode == 'edit') {
                $data = $exists->where('id', '=', $id)->first();
                if (!empty($data)) {
                    $positions = $this->getAvailablePositions($positionIds, $isInternal, $data->position_id);
                }
            } else {
                $positions = $this->getAvailablePositions($positionIds, $isInternal);
            }
        }

        return view('main.settings.bonus.royalty.form', [
            'setting' => $data,
            'mode' => $mode,
            'positions' => $positions,
            'category' => $category,
            'title' => $title,
            'modalHeader' => (($mode == 'new') ? 'Tambah' : 'Ubah') . ' Bonus Royalty',
        ]);
    }

    public function updateRoyalty(Request $request)
    {
        $mode = $request->get('mode');
        $category = $request->get('category');

        $responCode = 200;
        $responText = route("main.settings.bonus.royalty.index");

        if (!in_array($mode, ['new', 'edit']) || !in_array($category, ['internal', 'external'])) {
            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => 'Proses tidak dikenali.',
                'messageClass' => 'danger'
            ])->render();
        } else {
            $id = $request->get('id');
            $isNew = ($mode == 'new');
            $isInternal = ($category === 'internal');
            $exists = $this->neo->listSettingRoyalty($isInternal);
            $positionIds = $exists->pluck('position_id')->toArray();
            $data = null;
            $ignorePosition = null;
            $continue = true;

            if (!$isNew) {
                $data = $exists->where('id', '=', $id)->first();
                if (empty($data)) {
                    $responCode = 404;
                    $responText = view('partials.alert', [
                        'message' => 'Data tidak ditemukan.',
                        'messageClass' => 'danger'
                    ])->render();
                    $continue = false;
                } else {
                    $ignorePosition = $data->position_id;
                }
            }

            if ($continue === true) {
                $values = $request->except(['_token', 'id', 'mode', 'category']);
                $positions = $this->getAvailablePositions($positionIds, $isInternal, $ignorePosition)->pluck('id')->toArray();

                if (empty($positions)) $positions = [-9999];
                $inPosition = implode(',', $positions);

                $uniquePosition = (new Unique('setting_royalties', 'position_id'))
                    ->where('is_internal', $isInternal ? 1 : 0)
                    ->whereNull('deleted_at');

                if (!$isNew) {
                    $uniquePosition = $uniquePosition->ignore($data->id, 'id');
                }

                $validator = Validator::make($values, [
                    'position_id' => ['required', 'integer', "in:{$inPosition}", $uniquePosition],
                    'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
                ], [], [
                    'position_id' => 'Posisi',
                    'percent' => 'Bonus',
                ]);

                if (!$validator->fails()) {
                    $values['is_network'] = ($values['is_network'] == 1);
                    $values['is_internal'] = $isInternal;

                    DB::beginTransaction();

                    try {
                        $this->neo->updateSettingRoyalty($values, $data);

                        session([
                            'message' => 'Bonus Royalty ' . ($isInternal ? 'Internal' : 'External') . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
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

    // BONUS OVERRIDE
    public function indexOverride(Request $request)
    {
        $settings = $this->neo->listSettingOverride();

        $content = view('main.settings.bonus.override.index', [
            'settings' => $settings
        ])->render();

        return $this->getView('bonusOverride', $content, ['Override']);
    }

    public function editOverride(Request $request)
    {
        $mode = $request->get('mode');
        $data = null;

        if (in_array($mode, ['new', 'edit'])) {
            $id = $request->get('id');

            if ($mode == 'edit') {
                $data = $this->neo->settingOverrideById($id);
            }
        }

        return view('main.settings.bonus.override.form', [
            'setting' => $data,
            'mode' => $mode,
            'modalHeader' => (($mode == 'new') ? 'Tambah' : 'Ubah') . ' Bonus Override',
        ]);
    }

    public function updateOverride(Request $request)
    {
        $mode = $request->get('mode');

        $responCode = 200;
        $responText = route("main.settings.bonus.override.index");

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
                $data = $this->neo->settingOverrideById($id);
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

                $uniqueLevel = (new Unique('setting_overrides', 'level_id'))->whereNull('deleted_at');

                if (!$isNew) {
                    $uniqueLevel = $uniqueLevel->ignore($data->id, 'id');
                }

                $validator = Validator::make($values, [
                    'level_id' => ['required', 'integer', $uniqueLevel],
                    'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
                ], [], [
                    'level_id' => 'Level',
                    'percent' => 'Bonus',
                ]);

                if (!$validator->fails()) {
                    DB::beginTransaction();

                    try {
                        $this->neo->updateSettingOverride($values, $data);

                        session([
                            'message' => 'Bonus Override ' . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
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

    // BONUS TEAM
    public function indexBonusTeam(Request $request)
    {
        $settings = $this->neo->listSettingBonusTeam();

        $content = view('main.settings.bonus.team.index', [
            'settings' => $settings
        ])->render();

        return $this->getView('bonusTeam', $content, ['Team']);
    }

    public function editBonusTeam(Request $request)
    {
        $mode = $request->get('mode');
        $data = null;

        $positions = collect();

        if (in_array($mode, ['new', 'edit'])) {
            $id = $request->get('id');
            $exists = $this->neo->listSettingBonusTeam();
            $positionIds = $exists->pluck('position_id')->toArray();

            if ($mode == 'edit') {
                $data = $exists->where('id', '=', $id)->first();
                if (!empty($data)) {
                    $positions = $this->getAvailablePositions($positionIds, true, $data->position_id);
                }
            } else {
                $positions = $this->getAvailablePositions($positionIds, true);
            }
        }

        return view('main.settings.bonus.team.form', [
            'setting' => $data,
            'mode' => $mode,
            'positions' => $positions,
            'modalHeader' => (($mode == 'new') ? 'Tambah' : 'Ubah') . ' Bonus Team',
        ]);
    }

    public function updateBonusTeam(Request $request)
    {
        $mode = $request->get('mode');

        $responCode = 200;
        $responText = route("main.settings.bonus.team.index");

        if (!in_array($mode, ['new', 'edit'])) {
            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => 'Proses tidak dikenali.',
                'messageClass' => 'danger'
            ])->render();
        } else {
            $id = $request->get('id');
            $isNew = ($mode == 'new');
            $exists = $this->neo->listSettingBonusTeam();
            $positionIds = $exists->pluck('position_id')->toArray();
            $data = null;
            $ignorePosition = null;
            $continue = true;

            if (!$isNew) {
                $data = $exists->where('id', '=', $id)->first();
                if (empty($data)) {
                    $responCode = 404;
                    $responText = view('partials.alert', [
                        'message' => 'Data tidak ditemukan.',
                        'messageClass' => 'danger'
                    ])->render();
                    $continue = false;
                } else {
                    $ignorePosition = $data->position_id;
                }
            }

            if ($continue === true) {
                $values = $request->except(['_token', 'id', 'mode']);
                $positions = $this->getAvailablePositions($positionIds, true, $ignorePosition)->pluck('id')->toArray();

                if (empty($positions)) $positions = [-9999];
                $inPosition = implode(',', $positions);

                $uniquePosition = (new Unique('setting_bonus_teams', 'position_id'))->whereNull('deleted_at');

                if (!$isNew) {
                    $uniquePosition = $uniquePosition->ignore($data->id, 'id');
                }

                $validator = Validator::make($values, [
                    'position_id' => ['required', 'integer', "in:{$inPosition}", $uniquePosition],
                    'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
                ], [], [
                    'position_id' => 'Posisi',
                    'percent' => 'Bonus',
                ]);

                if (!$validator->fails()) {
                    DB::beginTransaction();

                    try {
                        $this->neo->updateSettingBonusTeam($values, $data);

                        session([
                            'message' => 'Bonus Team ' . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
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

    // BONUS SALE
    public function indexBonusSell(Request $request)
    {
        $setting = $this->neo->settingBonusSell();

        $content = view('main.settings.bonus.sell.index', [
            'setting' => $setting
        ])->render();

        return $this->getView('bonusSell', $content, ['Penjualan']);
    }

    public function editBonusSell(Request $request)
    {
        $setting = $this->neo->settingBonusSell();

        return view('main.settings.bonus.sell.form', [
            'setting' => $setting,
            'mode' => empty($setting) ? 'new' : 'edit',
            'modalHeader' => (empty($setting) ? 'Tambah' : 'Ubah') . ' Bonus Penjualan',
        ]);
    }

    public function updateBonusSell(Request $request)
    {
        $data = $this->neo->settingBonusSell();
        $isNew = empty($data);
        $values = $request->except(['_token']);

        $validator = Validator::make($values, [
            'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
        ], [], [
            'percent' => 'Bonus',
        ]);

        $responCode = 200;
        $responText = route("main.settings.bonus.sell.index");

        if (!$validator->fails()) {
            DB::beginTransaction();

            try {
                $this->neo->updateSettingBonusSell($values, $data);

                session([
                    'message' => 'Bonus Penjualan ' . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
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

        return response($responText, $responCode);
    }

    // BONUS DIRECT MEMBER
    public function indexBonusMitraPremiumShopping(Request $request)
    {
        $target = $request->settingBonusMitraShoppingTarget;
        $setting = $this->neo->settingBonusSell(true, $target);
        $key = '';
        $title = '';
        $routeKey = '';
        $arrType = Arr::get(BONUS_TYPE_MITRA_SHOPPINGS, $target, []);

        if (!empty($arrType)) {
            $key = $arrType['pageKey'];
            $routeKey = $arrType['routeKey'];
            $title = $arrType['title'];
        }

        $content = view('main.settings.bonus.mitra-shopping.index', [
            'setting' => $setting,
            'routeKey' => $routeKey,
        ])->render();

        return $this->getView($key, $content, [$title]);
    }

    public function editBonusMitraPremiumShopping(Request $request)
    {
        $target = $request->settingBonusMitraShoppingTarget;
        $setting = $this->neo->settingBonusSell(true, $target);

        $title = 'Bonus ';
        $routeKey = '';
        $arrType = Arr::get(BONUS_TYPE_MITRA_SHOPPINGS, $target, []);

        if (!empty($arrType)) {
            $title .= $arrType['title'];
            $routeKey = $arrType['routeKey'];
        }

        $postUrl = route('main.settings.bonus.mitraPremiumShopping.update', ['settingBonusMitraShoppingTarget' => $routeKey]);

        return view('main.settings.bonus.mitra-shopping.form', [
            'setting' => $setting,
            'mode' => empty($setting) ? 'new' : 'edit',
            'postUrl' => $postUrl,
            'modalHeader' => (empty($setting) ? 'Tambah' : 'Ubah') . " {$title}",
        ]);
    }

    public function updateBonusMitraPremiumShopping(Request $request)
    {
        $target = $request->settingBonusMitraShoppingTarget;
        $data = $this->neo->settingBonusSell(true, $target);
        $isNew = empty($data);
        $values = $request->except(['_token']);
        $values['is_direct'] = true;
        $values['target_id'] = $target;

        $validator = Validator::make($values, [
            'percent' => ['required', 'numeric', 'min:0', 'max:99.99'],
        ], [], [
            'percent' => 'Bonus',
        ]);

        $title = 'Bonus ';
        $routeKey = '';
        $arrType = Arr::get(BONUS_TYPE_MITRA_SHOPPINGS, $target, []);

        if (!empty($arrType)) {
            $title .= $arrType['title'];
            $routeKey = $arrType['routeKey'];
        }

        $responCode = 200;
        $responText = route('main.settings.bonus.mitraPremiumShopping.index', ['settingBonusMitraShoppingTarget' => $routeKey]);

        if (!$validator->fails()) {
            DB::beginTransaction();

            try {
                $this->neo->updateSettingBonusSell($values, $data);

                session([
                    'message' => $title . ' berhasil ' . ($isNew ? 'ditambahkan.' : 'diubah.'),
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

        return response($responText, $responCode);
    }
}
