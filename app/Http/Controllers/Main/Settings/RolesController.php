<?php

namespace App\Http\Controllers\Main\Settings;

use App\Helpers\AppPermission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RolesController extends Controller
{
    private AppPermission $appPermission;

    public function __construct()
    {
        $this->appPermission = app('appPermission');
    }

    private function getPositionList(Request $request)
    {
        $user = $request->user();
        $admins = [];

        if ($user->is_super_admin_user) {
            $admins[USER_TYPE_MASTER . '.0'] = 'Master Administrator';
        }

        if ($user->is_master_admin_user || $user->is_super_admin_user) {
            $admins[USER_TYPE_ADMIN] = [
                'Administrator',
                [
                    USER_TYPE_ADMIN . '.' . ADMIN_DIVISION_PUBLIC => Arr::get(ADMIN_DIVISION_LIST, ADMIN_DIVISION_PUBLIC),
                    USER_TYPE_ADMIN . '.' . ADMIN_DIVISION_FINANCIAL => Arr::get(ADMIN_DIVISION_LIST, ADMIN_DIVISION_FINANCIAL),
                    USER_TYPE_ADMIN . '.' . ADMIN_DIVISION_INVENTORY => Arr::get(ADMIN_DIVISION_LIST, ADMIN_DIVISION_INVENTORY),
                ]
            ];
        }

        return $admins;
    }

    public function index(Request $request)
    {
        $positions = $this->getPositionList($request);
        $currentAdminId = session('filter.adminId', array_keys($positions)[0]);

        return view('main.settings.roles.index', [
            'positions' => $positions,
            'currentAdminId' => $currentAdminId,
            'windowTitle' => 'Pengaturan Hak Akses',
            'breadcrumbs' => ['Pengaturan', 'Hak Akses']
        ]);
    }

    public function getForm(Request $request)
    {
        $adminId = $request->get('type_division', '0.0');
        list($userType, $divisionId) = explode('.', $adminId);
        $roles = $this->appPermission->groupedAdminRoles(intval($userType), intval($divisionId));

        session(['filter.adminId' => $adminId]);

        return view('main.settings.roles.form', [
            'roles' => $roles,
        ]);
    }

    public function update(Request $request)
    {

        $adminId = session('filter.adminId', '0.0');
        list($userType, $divisionId) = explode('.', $adminId);

        if (empty($userType) || ($userType == 0) || (($userType == USER_TYPE_ADMIN) && ($divisionId <= 0))) {
            return ajaxError('Pilihan jenis Administrator belum dipilih.');
        }

        $roles = $request->get('roles', []);

        DB::beginTransaction();
        try {
            $this->appPermission->updateAdminRoles(intval($userType), intval($divisionId), $roles ?: []);

            DB::commit();

            $message = view('partials.alert', [
                'message' => 'Hak Akses berhasil disimpan.',
                'messageClass' => 'success'
            ])->render();

            return response($message, 200);
        } catch (\Exception $e) {
            DB::rollBack();

            $message = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi. ' . (!isLive() ? $e->getMessage() : ''));

            return response($message, 500);
        }
    }
}
