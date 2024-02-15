<?php

namespace App\Http\Controllers\Main\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\NeoPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class UserAdminController extends Controller
{
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
        $admins = $this->getPositionList($request);

        if (empty($admins)) return pageError('Anda tidak dapat mengakses halaman pengaturan Staff Admin');

        $currentAdminId = session('filter.adminId', '0.0');

        return view('main.settings.admin.index', [
            'admins' => $admins,
            'currentAdminId' => $currentAdminId,
            'windowTitle' => 'Pengaturan Administrator',
            'breadcrumbs' => ['Pengaturan', 'Administrator']
        ]);
    }

    public function datatable(Request $request)
    {
        $user = $request->user();
        $adminId = $request->get('type_division', '0.0');
        $inTypes = [];

        if ($user->is_super_admin_user) {
            $inTypes[] = USER_TYPE_MASTER;
        }

        if ($user->is_master_admin_user || $user->is_super_admin_user) {
            $inTypes[] = USER_TYPE_ADMIN;
        }

        session(['filter.adminId' => $adminId]);

        $query = DB::table('users')
            ->where('user_group', '=', USER_GROUP_MAIN)
            ->whereIn('user_type', $inTypes);

        list($userType, $divisionId) = explode('.', $adminId);
        $isAll = ((empty($userType) || $userType == 0) && (empty($divisionId) || ($divisionId == 0)));

        if (!$isAll) {
            $query = $query->where('user_type', '=', $userType);

            if ($userType > USER_TYPE_MASTER) {
                if ($divisionId == ADMIN_DIVISION_PUBLIC) {
                    $query = $query->where(function ($where) {
                        return $where->whereIn('division_id', [0, ADMIN_DIVISION_PUBLIC]);
                    });
                } else {
                    $query = $query->where('division_id', '=', $divisionId);
                }
            }
        }

        $canEdit = hasPermission('main.settings.admin.edit');

        return datatables()->query($query)
            ->editColumn('name', function ($row) use ($userType) {
                $result = "<div>{$row->name}</div>";

                if ($userType == 0) {
                    $positionName = 'Administrator';
                    if ($row->user_type == USER_TYPE_MASTER) {
                        $positionName = 'Master ' . $positionName;
                    } else {
                        $divisionId = ($row->division_id > 0) ? $row->division_id : ADMIN_DIVISION_PUBLIC;
                        $positionName .= ' ' . Arr::get(ADMIN_DIVISION_LIST, $divisionId);
                    }

                    $result .= "<div class=\"text-decoration-underline text-primary\">{$positionName}</div>";
                }

                return new HtmlString($result);
            })
            ->editColumn('user_status', function ($row) {
                return new HtmlString(contentCheck(($row->user_status == USER_STATUS_ACTIVE)));
            })
            ->editColumn('view', function ($row) use ($canEdit) {
                $buttons = [];

                if ($canEdit) {
                    $routeEdit = route('main.settings.admin.edit', ['mainAdmin' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeEdit}\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt\"></i></button>";
                }

                if (count($buttons) > 0) return new HtmlString(implode('', $buttons));

                return '';
            })
            ->escapeColumns()
            ->toJson();
    }

    private function getNameOfType($userType, $divisionId)
    {
        $title = 'Administrator';
        if ($userType == USER_TYPE_MASTER) {
            $title = 'Master ' . $title;
        } else {
            $divisionId = ($divisionId > 0) ? $divisionId : ADMIN_DIVISION_PUBLIC;
            $title .= ' ' . Arr::get(ADMIN_DIVISION_LIST, $divisionId);
        }

        return $title;
    }

    private function getPositionFromData(array &$values, User $admin = null)
    {
        $userType = $admin ? $admin->user_type : USER_TYPE_ADMIN;
        $divisionId = $admin ? (($admin->division_id > 0) ? $admin->division_id : ADMIN_DIVISION_PUBLIC) : ADMIN_DIVISION_PUBLIC;

        if (empty($admin)) {
            list($userType, $divisionId) = explode('.', session('filter.adminId', '0.0'));
            if ($userType > 0) {
                if ($userType == USER_TYPE_MASTER) {
                    $divisionId = 0;
                } else {
                    if ($divisionId == 0) $divisionId = ADMIN_DIVISION_PUBLIC;
                }
            } else {
                $userType = USER_TYPE_ADMIN;
                $divisionId = ADMIN_DIVISION_PUBLIC;
            }

            $values['user_type'] = $userType;
            $values['division_id'] = $divisionId;
        }

        return (object) [
            'user_type' => $userType,
            'division_id' => $divisionId,
            'name' => $this->getNameOfType($userType, $divisionId)
        ];
    }

    private function getStepForm(string $form, string $postUrl, array $values, bool $disableSubmit, User $admin = null, array $others = null)
    {
        $position = $this->getPositionFromData($values, $admin);

        $vars = [
            'values' => $values,
            'data' => $admin,
            'position' => $position,
            'modalHeader' => empty($admin) ? 'Tambah' : 'Edit',
            'disableSubmit' => $disableSubmit,
            'postUrl' => $postUrl,
        ];

        if (!empty($others)) $vars = array_merge($vars, $others);

        return view($form, $vars);
    }

    private function validateDataPersonal(array $values, User $admin = null)
    {
        $uniqueEmail = new Unique('users', 'email');
        if (!empty($admin)) {
            $uniqueEmail = $uniqueEmail->ignore($admin->id, 'id');
        }

        $validator = Validator::make($values, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:100', 'email', $uniqueEmail],
            'phone' => ['nullable', 'digits_between:9,15', 'starts_with:08'],
        ], [], [
            'name' => 'Nama',
            'email' => 'Email',
            'phone' => 'Handphone',
        ]);

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    private function validateDataAuth(array $values, User $admin = null)
    {
        $uniqueUsername = new Unique('users', 'username');
        if (!empty($admin)) {
            $uniqueUsername = $uniqueUsername->ignore($admin->id, 'id');
        }
        $passwd = NeoPassword::min(6)->numbers()->letters();

        $validator = Validator::make($values, [
            'username' => ['required', 'string', 'max:30', 'regex:/^[\w-]*$/', $uniqueUsername],
            'password' => [empty($admin) ? 'required' : 'nullable', 'string', 'confirmed', $passwd],
        ], [], [
            'username' => 'Username',
            'password' => 'Password',
        ]);

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    private function validateDataPosition(array &$values, User $admin = null)
    {
        list($vType, $vDivision) = explode('.', $values['types']);

        $typeRule = 'in:' . USER_TYPE_MASTER;
        $divisionRule = 'in:0';

        if ($vType == USER_TYPE_ADMIN) {
            $typeRule = 'in:' . USER_TYPE_ADMIN;
            $divisionRule = 'in:' . implode(',', array_keys(ADMIN_DIVISION_LIST));
        }

        $values['user_type'] = $vType;
        $values['division_id'] = $vDivision;


        $validator = Validator::make($values, [
            'user_type' => ['required', $typeRule],
            'division_id' => ['required', $divisionRule],
        ], [], [
            'user_type' => 'Posisi',
            'division_id' => 'Posisi',
        ]);

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    public function create(Request $request)
    {
        $position = session('filter.adminId', '0.0');
        if (empty($position) || ($position == '0.0')) {
            return ajaxError('Silahkan tentukan posisi administrator terlebih dahulu.', 500);
        }

        return $this->getStepForm('main.settings.admin.forms.personal', route('main.settings.admin.store'), [], true);
    }

    public function store(Request $request)
    {
        $formStep = $request->get('form_step');
        $values = $request->except(['_token', 'form_step']);

        if ($formStep != 'confirm') {
            $fn = str_replace(' ', '', ucwords(str_replace('-', ' ', $formStep)));

            $fn = 'validateData' . $fn;
            $validation = $this->$fn($values);

            if (!$validation->valid) {
                return response($validation->message, 400);
            }

            $nextStep = '';

            if ($formStep == 'personal') {
                $nextStep = 'auth';
            } elseif ($formStep == 'auth') {
                $nextStep = 'confirm';
            }

            $storeRoute = route('main.settings.admin.store');
            $others = [];

            if ($nextStep == 'auth') {
                return $this->getStepForm('main.settings.admin.forms.auth', $storeRoute, $values, true, null, $others);
            } elseif ($nextStep == 'confirm') {
                return $this->getStepForm('main.settings.admin.forms.confirm', $storeRoute, $values, false, null, $others);
            }

            return view('main.settings.admin.forms.unknown', ['modalHeader' => 'Tambah']);
        }

        $time = time();
        $values['password'] = Hash::make($values['password']);
        $values['user_group'] = USER_GROUP_MAIN;
        $values['user_status'] = USER_STATUS_ACTIVE;
        $values['status_at'] = $time;

        $title = $this->getNameOfType($values['user_type'], $values['division_id']);
        $responCode = 200;
        $responText = route("main.settings.admin.index");

        DB::beginTransaction();
        try {
            User::create($values);

            session([
                'message' => "{$title} berhasil ditambahkan.",
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

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        $admin = $request->mainAdmin;

        $updateRoute = route('main.settings.admin.update', ['mainAdmin' => $admin->id]);

        return $this->getStepForm('main.settings.admin.forms.edit', $updateRoute, [], true, $admin, [
            'showFooter' => false,
            'showPosition' => false,
        ]);
    }

    public function update(Request $request)
    {
        $admin = $request->mainAdmin;
        $formStep = $request->get('form_step');
        $values = $request->except(['_token', 'form_step']);

        if ($formStep != 'confirm') {

            if ($formStep != 'edit') {
                $fn = str_replace(' ', '', ucwords(str_replace('-', ' ', $formStep)));

                $fn = 'validateData' . $fn;
                $validation = $this->$fn($values, $admin);

                if (!$validation->valid) {
                    return response($validation->message, 400);
                }
            }

            $nextStep = 'confirm';

            if ($formStep == 'edit') {
                $nextStep = $values['edit_choice'];
            }

            $storeRoute = route('main.settings.admin.update', ['mainAdmin' => $admin->id]);
            $others = [];

            if ($nextStep == 'personal') {
                return $this->getStepForm('main.settings.admin.forms.personal', $storeRoute, $values, true, $admin, $others);
            } elseif ($nextStep == 'position') {
                $others['admins'] = $this->getPositionList($request);
                return $this->getStepForm('main.settings.admin.forms.position', $storeRoute, $values, true, $admin, $others);
            } elseif ($nextStep == 'auth') {
                return $this->getStepForm('main.settings.admin.forms.auth', $storeRoute, $values, true, $admin, $others);
            } elseif ($nextStep == 'confirm') {
                if (isset($values['user_type']) && isset($values['division_id'])) {
                    $values['position_name'] = $this->getNameOfType($values['user_type'], $values['division_id']);
                }
                return $this->getStepForm('main.settings.admin.forms.confirm', $storeRoute, $values, false, $admin, $others);
            }

            return view('main.settings.admin.forms.unknown', ['modalHeader' => 'Edit']);
        }

        $position = $this->getPositionFromData($values, $admin);
        $title = $position->name;

        if ($values['edit_choice'] == 'personal') {
            $title = 'Data ' . $title;
        } elseif ($values['edit_choice'] == 'auth') {
            if (!is_null($values['password']) && $values['password'] != '') {
                $values['password'] = Hash::make($values['password']);
            } else {
                unset($values['password']);
            }
            $title = 'Autentikasi ' . $title;
        } elseif ($values['edit_choice'] == 'position') {
            $title = 'Posisi Administrator';
        }

        $responCode = 200;
        $responText = route("main.settings.admin.index");

        DB::beginTransaction();
        try {
            $admin->timestamps = true;
            $admin->update($values);

            session([
                'message' => "{$title} berhasil ditambahkan.",
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

        return response($responText, $responCode);
    }
}
