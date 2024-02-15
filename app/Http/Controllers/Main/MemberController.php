<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchMember;
use App\Models\User;
use App\Rules\NeoPassword;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Spatie\SimpleExcel\SimpleExcelWriter;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $currentPositionId = session('filter.positionId', USER_INT_GM);
        $currentBranchId = session('filter.branchId', -1);

        session(['structure.internal.data' => $currentPositionId]);

        return view('main.members.index', [
            'currentPositionId' => $currentPositionId,
            'currentBranchId' => $currentBranchId,
            'windowTitle' => 'Daftar Anggota',
            'breadcrumbs' => ['Anggota', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $isForUpline = ($request->get('modal_upline', 0) == 1);
        $appStructure = app('appStructure');

        if ($isForUpline === true) {
            $positionId = intval(session('filter.positionId', USER_INT_GM));
            $currentUplineId = $request->get('current_upline');

            $baseQuery = DB::table('users')
                ->selectRaw("
                    users.id, users.username, users.name, users.email, users.user_status, 
                    users.position_int, users.position_ext
                ")
                ->where('users.position_int', '<=', $positionId);

            $query = DB::table(DB::raw("({$baseQuery->toSql()}) as uplines"))
                ->selectRaw('
                    id, username, name, email, user_status, 
                    position_int, position_ext
                ')
                ->mergeBindings($baseQuery);

            $result = datatables()->query($query)
                ->editColumn('position_int', function ($row) use ($appStructure) {
                    $intPositionName = $appStructure->nameById(true, intval($row->position_int));

                    $result = "<div>{$intPositionName}</div>";

                    if ($row->position_int == USER_INT_MGR) {
                        $extPositionName = $appStructure->nameById(false, intval($row->position_ext));
                        $result .= "<div class=\"text-decoration-underline text-primary\">{$extPositionName}</div>";
                    }

                    $result = new HtmlString($result);

                    return $result;
                })
                ->editColumn('user_status', function ($row) {
                    return new HtmlString(contentCheck($row->user_status == USER_STATUS_ACTIVE));
                })
                ->editColumn('id', function ($row) use ($currentUplineId) {
                    $check = '<input class="text-primary" type="radio" name="upline_id" value="' . $row->id . '" %s>';
                    $check = sprintf($check, ($currentUplineId == $row->id) ? 'checked' : '');

                    return new HtmlString($check);
                })
                ->escapeColumns();

            return $result->toJson();
        }

        $canEdit = hasPermission('main.member.edit');
        $positionId = intval($request->get('position_id', -1));

        session(['filter.positionId' => $positionId]);

        $baseQuery = DB::table('users')
            ->leftJoin('branch_members', function ($join) {
                $join->on('branch_members.user_id', '=', 'users.id')
                    ->where('branch_members.is_active', '=', true);
            })
            ->leftJoin('branches', function ($join) {
                $join->on('branches.id', '=', 'branch_members.branch_id')
                    ->where('branches.is_active', '=', true);
            })
            ->leftJoin(DB::raw('users as upline'), 'upline.id', '=', 'users.upline_id')
            ->selectRaw("
                users.id, users.username, users.name, users.email, users.phone, users.user_status, 
                users.position_int, 
                null as branch_name, 
                upline.name as upline_name,
                upline.position_int as upline_position_int,
                GROUP_CONCAT(COALESCE(branches.name, '')) as manager_branches,
                GROUP_CONCAT(COALESCE(branch_members.position_ext, '')) as manager_positions,
                GROUP_CONCAT(COALESCE(branch_members.manager_type, '')) as manager_types
            ")
            ->where('users.user_group', '=', USER_GROUP_MEMBER)
            ->where('users.user_type', '=', USER_TYPE_MEMBER)
            ->groupBy([
                'users.id', 'users.username', 'users.name', 'users.email', 'users.phone', 'users.user_status',
                'users.position_int', 'users.position_ext',
                'upline.name', 'upline.position_int', 'upline.position_ext'
            ]);

        $printUserPosition = true;
        if ($positionId > 0) {
            $printUserPosition = false;
            $baseQuery = $baseQuery->where('users.position_int', '=', $positionId);
        }

        $posMangerList = $appStructure->getExternalManagerOptions();

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as members"))->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('name', function ($row) use ($appStructure, $printUserPosition) {
                $result = "<div>{$row->name}</div>";
                if ($printUserPosition) {
                    $positionName = $appStructure->nameById(true, intval($row->position_int));
                    $result .= "<div class=\"text-decoration-underline text-primary\">{$positionName}</div>";
                }

                return new HtmlString($result);
            })
            ->editColumn('upline_name', function ($row) use ($appStructure) {
                if (!empty($row->upline_name)) {
                    $result = "<div>{$row->upline_name}</div>";
                    $positionName = $appStructure->nameById(true, intval($row->upline_position_int));
                    $result .= "<div class=\"text-decoration-underline text-primary\">{$positionName}</div>";

                    return new HtmlString($result);
                }

                return '-';
            })
            ->editColumn('branch_name', function ($row) use ($appStructure) {
                $branches = explode(',', $row->manager_branches);

                if (!empty($branches) && ($row->position_int >= USER_INT_MGR)) {
                    $content = '<ul class="ps-3 mb-0">%s</ul>';
                    $list = '';
                    $isManager = ($row->position_int == USER_INT_MGR);
                    $postList = explode(',', $row->manager_positions);
                    $typeList = explode(',', $row->manager_types);

                    $branchCollect = collect();
                    foreach ($branches as $index => $branchName) {
                        $branchCollect->push((object) [
                            'name' => $branchName,
                            'position' => $isManager ? $appStructure->codeById(false, intval($postList[$index])) : null,
                            'type' => $isManager ? Arr::get(USER_BRANCH_MANAGER_CODES, $typeList[$index]) : null
                        ]);
                    }

                    $branchCollect = $branchCollect->sortBy('name');

                    foreach ($branchCollect as $branch) {
                        $posBranch = '';
                        if (!empty($branch->position) || !empty($branch->type)) {
                            $array = [];
                            if (!empty($branch->position)) $array[] = $branch->position;
                            if (!empty($branch->type)) $array[] = $branch->type;
                            $str = implode(' - ', $array);

                            $posBranch = "<div class=\"ms-2\">(<span class=\"text-primary\">{$str}</span>)</div>";
                        }
                        $list .= "<li><div class=\"d-flex flex-nowrap text-nowrap\"><div>{$branch->name}</div>{$posBranch}</div></li>";
                    }

                    return new HtmlString(sprintf($content, $list));
                }

                return '-';
            })
            ->editColumn('user_status', function ($row) {
                return new HtmlString(contentCheck($row->user_status == USER_STATUS_ACTIVE));
            })
            ->addColumn('view', function ($row) use ($canEdit) {
                $buttons = [];

                if ($canEdit) {
                    $routeEdit = route('main.member.edit', ['userMember' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeEdit}\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt\"></i></button>";
                }

                // $routeDetail = route('main.member.detail', ['userMember' => $row->id]);
                // $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDetail}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                if (count($buttons) > 0) return new HtmlString(implode('', $buttons));

                return '';
            })->escapeColumns();

        return $result->toJson();
    }

    private function getAvailableBranches(int $positionId, int $upline_id): Collection
    {
        if ($positionId == USER_INT_MGR) return Branch::byActive()->orderBy('name')->get();

        return Branch::byActive()->orderBy('name')
            ->whereHas('members', function ($q) use ($upline_id) {
                return $q->byActive()->where('user_id', '=', $upline_id);
            })->get();
    }

    private function getPositionFromData(array &$values, User $member = null)
    {
        if (!isset($values['position_id'])) {
            $positionId = $member ? $member->position_int : intval(session('filter.positionId', USER_INT_GM));
            $values['position_id'] = $positionId;
        } else {
            $positionId = $member ? $member->position_int : intval($values['position_id']);
        }

        return app('appStructure')->getDataById(true, $positionId);
    }

    private function getStepForm(string $form, string $postUrl, array $values, bool $disableSubmit, User $member = null, array $others = null)
    {
        $structure = $this->getPositionFromData($values, $member);

        $vars = [
            'values' => $values,
            'data' => $member,
            'structure' => $structure,
            'modalHeader' => empty($member) ? 'Tambah' : 'Edit',
            'disableSubmit' => $disableSubmit,
            'postUrl' => $postUrl,
        ];

        if (!empty($others)) $vars = array_merge($vars, $others);

        return view($form, $vars);
    }

    private function validateDataMember(array $values, User $member = null)
    {
        $validator = Validator::make($values, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:100', 'email'],
            'phone' => ['required', 'digits_between:9,15', 'starts_with:08'],
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

    private function validateDataAuth(array $values, User $member = null)
    {
        $uniqueUsername = new Unique('users', 'username');
        if (!empty($member)) {
            $uniqueUsername = $uniqueUsername->ignore($member->id, 'id');
        }
        $passwd = NeoPassword::min(6)->numbers()->letters();

        $validator = Validator::make($values, [
            'username' => ['required', 'string', 'max:30', 'regex:/^[\w-]*$/', $uniqueUsername],
            'password' => [empty($member) ? 'required' : 'nullable', 'string', 'confirmed', $passwd],
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

    private function validateDataUpline(array $values, User $member = null)
    {
        $uplineExists = new Exists('users', 'id');
        $selectBranch = (($positionId = $values['position_id']) >= USER_INT_MGR);

        $uplineExists = $uplineExists
            ->where('user_group', USER_GROUP_MEMBER)
            ->where('user_type', USER_TYPE_MEMBER)
            ->where(function ($q) use ($positionId, $selectBranch) {
                return $q->where('position_int', $selectBranch ? '<=' : '<', $positionId);
            });

        $validator = Validator::make($values, [
            'upline_id' => ['required', $uplineExists]
        ], [], [
            'upline_id' => 'Upline',
        ]);

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    private function validateDataBranch(array &$values, User $member = null)
    {
        $branches = $this->getAvailableBranches($values['position_id'], $values['upline_id']);
        $inBranch = !empty($branches) ? implode(',', $branches->pluck('id')->toArray()) : [0];
        $inPosition = implode(',', [USER_EXT_DIST, USER_EXT_AG]);
        $inType = implode(',', [USER_BRANCH_MANAGER_QUARTERBACK, USER_BRANCH_MANAGER_TENANT]);

        $trueBranch = [
            'branch_ids' => [],
            'branch_positions' => [],
            'branch_types' => [],
        ];

        $isManager = ($values['position_id'] == USER_INT_MGR);
        $attrMassages = [];
        $storedBranch = [];

        foreach ($values['branch_ids'] as $index => $value) {
            $branch = $branches->where('id', '=', $value)->first();
            if (!empty($branch) && in_array($value, $values['branch_checks'])) {
                $storedBranch[] = $branch;
                $trueBranch['branch_ids'][] = $value;

                if ($isManager) {
                    $trueBranch['branch_positions'][] = $values['branch_positions'][$index];
                    $trueBranch['branch_types'][] = $values['branch_types'][$index];
                }
            }
        }

        $values = array_merge($values, $trueBranch);

        unset($values['branch_checks']);

        $rules = [];
        if ($isManager) {
            $rules = [
                'branch_positions' => ['array', 'min:1'],
                'branch_positions.*' => ['required', "in:{$inPosition}"],
                'branch_types' => ['required', 'array', 'min:1'],
                'branch_types.*' => ['required', "in:{$inType}"],
            ];
        } else {
            if (isset($values['branch_positions'])) unset($values['branch_positions']);
            if (isset($values['branch_types'])) unset($values['branch_types']);
        }

        if (!empty($storedBranch)) {
            foreach ($storedBranch as $index => $branch) {
                $branchName = $branch->name;
                $attrMassages["branch_ids.{$index}"] = "Kantor Cabang <b>{$branchName}</b>";
                $attrMassages["branch_positions.{$index}"] = "<b>Posisi Manager</b> untuk Kantor Cabang <b>{$branchName}</b>";
                $attrMassages["branch_types.{$index}"] = "<b>Jenis Manager</b> untuk Kantor Cabang <b>{$branchName}</b>";
            }
        }

        $validator = Validator::make($values, array_merge([
            'branch_ids' => ['required', 'array', 'min:1'],
            'branch_ids.*' => ['required', 'integer', 'distinct', "in:{$inBranch}"],
        ], $rules), [
            'branch_ids.required' => ':attribute harus dipilih.',
            'branch_ids.*.required' => ':attribute harus dipilih.',
            'branch_ids.*.in' => ':attribute tidak tersedia.',
            'branch_positions.required' => ':attribute harus dipilih.',
            'branch_positions.*.required' => ':attribute harus dipilih.',
            'branch_positions.*.in' => ':attribute tidak tersedia.',
            'branch_types.required' => ':attribute harus dipilih.',
            'branch_types.*.required' => ':attribute harus dipilih.',
            'branch_types.*.in' => ':attribute tidak tersedia.',
        ], array_merge([
            'branch_ids' => 'Kantor Cabang',
            'branch_positions' => 'Posisi Manager',
            'branch_types' => 'Jenis Manager',
        ], $attrMassages));

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    private function validateDataUpgradePosition(array $values, User $member = null)
    {
        $currentPositionId = $member ? $member->position_int : 1000;

        $positions = app('appStructure')->getAllData(true)->where('id', '<', $currentPositionId);
        $inPositions = implode(',', $positions->pluck('id')->toArray());

        $validator = Validator::make($values, [
            'position_int' => ['required', "in:{$inPositions}"]
        ], [], [
            'position_int' => 'Posisi',
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
        $positionId = session('filter.positionId', -1);

        if (empty($positionId) || ($positionId <= 0)) {
            return ajaxError('Silahkan tentukan posisi anggota terlebih dahulu.', 500);
        }

        return $this->getStepForm('main.members.forms.member', route('main.member.store'), [], true);
    }

    public function store(Request $request)
    {
        $formStep = $request->get('form_step');
        $values = $request->except(['_token', 'form_step']);
        $positionId = intval($values['position_id']);

        if ($formStep != 'confirm') {
            $fn = str_replace(' ', '', ucwords(str_replace('-', ' ', $formStep)));

            $fn = 'validateData' . $fn;
            $validation = $this->$fn($values);

            if (!$validation->valid) {
                return response($validation->message, 400);
            }

            $nextStep = '';

            if ($formStep == 'member') {
                $nextStep = 'auth';
            } elseif ($formStep == 'auth') {
                $nextStep = ($positionId > USER_INT_GM) ? 'upline' : 'confirm';
            } elseif ($formStep == 'upline') {
                $nextStep = ($positionId >= USER_INT_MGR) ? 'branch' : 'confirm';
            } elseif ($formStep == 'branch') {
                $nextStep = 'confirm';
            }

            $storeRoute = route('main.member.store');
            $others = [];

            if ($nextStep == 'auth') {
                return $this->getStepForm('main.members.forms.auth', $storeRoute, $values, true, null, $others);
            } elseif ($nextStep == 'upline') {
                return $this->getStepForm('main.members.forms.upline', $storeRoute, $values, true, null, $others);
            } elseif ($nextStep == 'branch') {
                $others['branches'] = $this->getAvailableBranches($positionId, intval($values['upline_id']));
                return $this->getStepForm('main.members.forms.branch', $storeRoute, $values, true, null, $others);
            } elseif ($nextStep == 'confirm') {
                if (isset($values['branch_ids'])) {
                    $others['branches'] = Branch::whereIn('id', $values['branch_ids'])->byActive()->orderBy('name')->get();
                }

                if (isset($values['upline_id'])) {
                    $others['upline'] = User::byId($values['upline_id'])->first();
                }

                if (isset($values['manager_type'])) {
                    $others['managerType'] = Arr::get(USER_BRANCH_MANAGER_TYPES, $values['manager_type'], '-');
                }

                if (isset($values['position_ext'])) {
                    $array = app('appStructure')->getExternalManagerOptions();
                    $others['positionExt'] = Arr::get($array, $values['position_ext'], '-');
                }

                return $this->getStepForm('main.members.forms.confirm', $storeRoute, $values, false, null, $others);
            }

            return view('main.members.forms.unknown', ['modalHeader' => 'Tambah']);
        }

        $appStructure = app('appStructure');
        $internalStructure = $appStructure->getDataById(true, $positionId);
        $externalStructure = $appStructure->getDataByCode(false, $internalStructure->code);

        $time = time();
        $values['password'] = Hash::make($values['password']);
        $values['user_group'] = USER_GROUP_MEMBER;
        $values['user_type'] = USER_TYPE_MEMBER;
        $values['user_status'] = USER_STATUS_ACTIVE;
        $values['position_int'] = $positionId;
        $values['status_at'] = $time;
        $values['activated'] = true;
        $values['activated_at'] = $time;
        if ($internalStructure->id < USER_INT_MGR) {
            $values['position_ext'] = $externalStructure ? $externalStructure->id : null;
        }

        $title = $internalStructure->name;
        $responCode = 200;
        $responText = route("main.member.index");

        $branches = [];
        $values['branch_id'] = null;

        if ($internalStructure->id >= USER_INT_MGR) {
            foreach ($values['branch_ids'] as $index => $branchId) {
                $posId = ($positionId == USER_INT_MGR) ? $values['branch_positions'][$index] : null;
                $typeId = ($positionId == USER_INT_MGR) ? $values['branch_types'][$index] : null;

                $branches[] = [
                    'branch_id' => $branchId,
                    'position_ext' => $posId,
                    'manager_type' => $typeId,
                    'active_at' => $time,
                ];
            }

            if (!in_array($internalStructure->id, [USER_INT_MGR, USER_INT_AM])) {
                $values['branch_id'] = $values['branch_ids'][0];
            }
        }

        $upline = null;

        if (isset($values['upline_id'])) {
            $upline = User::byId($values['upline_id'])->first();
        }

        $tree = $upline ? $upline->structure : null;

        DB::beginTransaction();
        try {
            $newUser = User::create($values);

            if (!empty($branches)) {
                foreach ($branches as $branch) {
                    $branch['user_id'] = $newUser->id;
                    BranchMember::create($branch);
                }
            }

            $newUser->structure()->create(['parent_id' => $tree ? $tree->id : null]);

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
        $member = $request->userMember;

        $updateRoute = route('main.member.update', ['userMember' => $member->id]);

        return $this->getStepForm('main.members.forms.edit', $updateRoute, [], true, $member, [
            'showFooter' => false,
            'showPosition' => false,
        ]);
    }

    public function update(Request $request)
    {
        $member = $request->userMember;
        $values = $request->except(['_token', 'form_step']);
        $formStep = $request->get('form_step');
        $positionId = $member->position_int;

        if ($formStep != 'confirm') {

            if ($formStep != 'edit') {
                $fn = str_replace(' ', '', ucwords(str_replace('-', ' ', $formStep)));

                $fn = 'validateData' . $fn;
                $validation = $this->$fn($values, $member);

                if (!$validation->valid) {
                    return response($validation->message, 400);
                }
            }

            $nextStep = '';

            if ($formStep == 'member') {
                $nextStep = 'confirm';
            } elseif ($formStep == 'auth') {
                $nextStep = 'confirm';
            } elseif ($formStep == 'upline') {
                if (!isset($values['upline_id'])) $values['upline_id'] = $member->upline_id;
                if ($values['upline_id'] != $member->upline_id) {
                    $upline = User::byId($values['upline_id'])->first();
                } else {
                    $upline = $member->upline;
                }
                $nextStep = (($positionId >= USER_INT_MGR) || ($upline->position_int >= USER_INT_MGR)) ? 'branch' : 'confirm';
            } elseif ($formStep == 'branch') {
                $nextStep = 'confirm';
            } elseif ($formStep == 'upgrade-position') {
                $nextStep = 'confirm';
            } elseif ($formStep == 'edit') {
                $nextStep = $values['edit_choice'];
            }

            $updateRoute = route('main.member.update', ['userMember' => $member->id]);
            $others = ['showPosition' => false];

            if ($nextStep == 'member') {
                return $this->getStepForm('main.members.forms.member', $updateRoute, $values, true, $member, $others);
            } elseif ($nextStep == 'auth') {
                return $this->getStepForm('main.members.forms.auth', $updateRoute, $values, true, $member, $others);
            } elseif ($nextStep == 'upline') {
                return $this->getStepForm('main.members.forms.upline', $updateRoute, $values, true, $member, $others);
            } elseif ($nextStep == 'branch') {
                if (!isset($values['upline_id'])) $values['upline_id'] = $member->upline_id;
                $others['branches'] = $this->getAvailableBranches($positionId, intval($values['upline_id']));
                $others['currentBranchIds'] = $member->activeBranches->pluck('branch_id')->toArray();
                return $this->getStepForm('main.members.forms.branch', $updateRoute, $values, true, $member, $others);
            } elseif ($nextStep == 'upgrade-position') {
                $others['showPosition'] = true;
                $others['positions'] = app('appStructure')->getAllData(true)->where('id', '<', $member->position_int);
                return $this->getStepForm('main.members.forms.upgrade-position', $updateRoute, $values, true, $member, $others);
            } elseif ($nextStep == 'confirm') {
                if (isset($values['branch_ids'])) {
                    $others['branches'] = Branch::whereIn('id', $values['branch_ids'])->byActive()->orderBy('name')->get();
                }

                if (isset($values['upline_id'])) {
                    $others['upline'] = User::byId($values['upline_id'])->first();
                }

                if (isset($values['position_int'])) {
                    $array = app('appStructure')->getAllData(true);
                    $others['positionInt'] = Arr::get($array, $values['position_int'], null);
                }

                return $this->getStepForm('main.members.forms.confirm', $updateRoute, $values, false, $member, $others);
            }

            return view('main.members.forms.unknown', ['modalHeader' => 'Edit']);
        }

        $structure = $member->data_internal_position;
        $title = $structure->name;
        $selectBranch = false;
        $branchInsert = [];
        $branchEnable = [];
        $branchDisable = [];
        $responCode = 200;
        $responText = route("main.member.index");

        if ($values['edit_choice'] == 'member') {
            $title = 'Data Member';
        } elseif ($values['edit_choice'] == 'auth') {
            if (!is_null($values['password']) && $values['password'] != '') {
                $values['password'] = Hash::make($values['password']);
            } else {
                unset($values['password']);
            }
            $title = 'Autentikasi Member';
        } elseif (in_array($values['edit_choice'], ['upline', 'branch'])) {
            if ($values['upline_id'] != $member->upline_id) {
                $upline = User::byId($values['upline_id'])->first();
            } else {
                $upline = $member->upline;
            }

            $selectBranch = ($positionId >= USER_INT_MGR);
            if ($selectBranch) {
                if (!in_array($positionId, [USER_INT_MGR, USER_INT_AM])) {
                    $values['branch_id'] = $values['branch_ids'][0];
                }

                $inputBranchIds = array_map(function ($x) {
                    return intval($x);
                }, array_values($values['branch_ids']));

                $branchMembers = $member->activeBranches;

                foreach ($branchMembers as $branch) {
                    if (!in_array($branch->branch_id, $inputBranchIds)) {
                        $branchDisable[] = [
                            'id' => $branch->id,
                            'is_active' => false,
                        ];
                    }
                }

                $time = date('Y-m-d H:i:s');

                foreach ($inputBranchIds as $index => $branchId) {
                    $branch = $branchMembers->where('branch_id', '=', $branchId)->first();
                    $posId = ($positionId == USER_INT_MGR) ? $values['branch_positions'][$index] : null;
                    $typeId = ($positionId == USER_INT_MGR) ? $values['branch_types'][$index] : null;

                    if (empty($branch)) {
                        $branchInsert[] = [
                            'user_id' => $member->id,
                            'branch_id' => $branchId,
                            'position_ext' => $posId,
                            'manager_type' => $typeId,
                            'active_at' => $time,
                        ];
                    } else {
                        $branchEnable[] = [
                            'id' => $branch->id,
                            'is_active' => true,
                            'position_ext' => $posId,
                            'manager_type' => $typeId,
                            'active_at' => $time,
                        ];;
                    }
                }
            }

            if ($values['edit_choice'] == 'upline') {
                $title = 'Upline Member';
            } else {
                $title = 'Kantor Cabang Member';
            }
        }

        if (isset($values['position_int']) && ($values['position_int'] < USER_INT_MGR)) {
            $values['position_ext'] = $values['position_int'];
        } else {
            $values['position_ext'] = null;
        }

        // dd($values);
        $uplineChange = false;
        $newTree = null;

        if (($values['edit_choice'] == 'upline') && ($values['current_upline_id'] != $values['upline_id'])) {
            $upline = User::byId($values['upline_id'])->first();
            $newTree = $upline ? $upline->structure : null;
            $uplineChange = true;
        }

        DB::beginTransaction();
        try {
            $member->timestamps = true;
            $member->update($values);

            if ($selectBranch) {
                if (!empty($branchDisable)) {
                    foreach ($branchDisable as $brDisable) {
                        $brId = $brDisable['id'];
                        unset($brDisable['id']);
                        BranchMember::byId($brId)->update($brDisable);
                    }
                }

                if (!empty($branchEnable)) {
                    foreach ($branchEnable as $brEnable) {
                        $brId = $brEnable['id'];
                        unset($brEnable['id']);
                        BranchMember::byId($brId)->update($brEnable);
                    }
                }

                if (!empty($branchInsert)) {
                    foreach ($branchInsert as $brInsert) {
                        BranchMember::create($brInsert);
                    }
                }
            }

            if ($uplineChange === true) {
                $member->structure->moveTo(0, $newTree);
            }

            session([
                'message' => "{$title} berhasil diubah.",
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

    private function downloadQuery(Request $request)
    {
        $positionId = intval($request->get('position_id', -1));

        $query = DB::table('users')
            ->leftJoin(DB::raw('users as upline'), 'upline.id', '=', 'users.upline_id')
            ->leftJoin('branch_members', function ($join) {
                $join->on('branch_members.user_id', '=', 'users.id')
                    ->where('branch_members.is_active', '=', true);
            })
            ->leftJoin('branches', function ($join) {
                $join->on('branches.id', '=', 'branch_members.branch_id')
                    ->where('branches.is_active', '=', true);
            })
            ->selectRaw('
            users.id, users.username, users.name, users.email, users.phone,
            users.position_int, users.position_ext,
            users.upline_id, upline.name as upline_name, 
            upline.position_int as upline_position_int,
            upline.position_ext as upline_position_ext,
            branch_members.branch_id,
            branches.name as branch_name
            ')
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_group', '=', USER_GROUP_MEMBER)
            ->where('users.user_type', '=', USER_TYPE_MEMBER);

        if ($positionId > 0) {
            $query = $query->where('users.position_int', '=', $positionId);
        }

        return (object) [
            'positionId' => $positionId,
            'rows' => $query
                ->orderBy('users.position_int')
                ->orderBy('upline.position_int')
                ->orderBy(DB::raw('(CASE WHEN upline.position_ext is null THEN 100 ELSE upline.position_ext END)'))
                ->orderBy('upline.username')
                ->orderBy('users.name')
                ->orderBy('users.username')
                ->orderBy('branches.name')
                ->get()
        ];
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->downloadQuery($request);
        $rows = $data->rows;

        if ($rows->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $today = Carbon::today();
        $tglReport = formatFullDate($today);
        $tglName = $today->format('Ymd');
        $appStructure = app('appStructure');
        $posisiName = (($posisiId = $data->positionId) > 0) ? $appStructure->nameById(true, $data->positionId) : '';
        $positionName = empty($posisiName) ? '' : strtolower(str_replace(' ', '_', "-{$posisiName}"));

        $downloadName = "Member{$positionName}-{$tglName}.xlsx";
        $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->setFontName('tahoma')->build();
        $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setFontName('tahoma')->build();
        $rowStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->build();

        $excel = SimpleExcelWriter::streamDownload($downloadName)->noHeaderRow()
            ->addRow([
                '',
            ])
            ->addRow([
                'LAPORAN DATA ANGGOTA',
            ], $titleStyle)
            ->addRow([
                'Tanggal', ':', $tglReport,
            ], $titleStyle);

        if ($posisiId > 0) {
            $excel = $excel
                ->addRow([
                    'Posisi', ':', $posisiName
                ], $titleStyle);
        }

        $printPosisi = ($posisiId <= 0);
        $positionId = 0;
        $userId = 0;

        $headers = [];
        if ($printPosisi) {
            $headers = ['Posisi'];
        }

        $headers = array_merge($headers, [
            'Nama', 'Username', 'Email', 'Handphone', 'Upline', 'Cabang'
        ]);

        $excel = $excel
            ->addRow([
                '',
            ])
            ->addRow($headers, $headerStyle);

        foreach ($rows as $row) {
            $branchName = $row->branch_name;
            if ($row->position_int >= USER_INT_MGR) {
                if (empty($branchName)) continue;
            }

            $branchName = $branchName ?? '-';

            $isSameUser = ($row->id == $userId);
            $positionInt = intval($row->position_int);
            $values = [
                $isSameUser ? '' : $row->name,
                $isSameUser ? '' : $row->username,
                $isSameUser ? '' : $row->email,
                $isSameUser ? '' : $row->phone,
                $isSameUser ? '' : $row->upline_name,
                $branchName
            ];

            $cells = [];

            if ($printPosisi) {
                $cells = [''];

                if ($positionId != $positionInt) {
                    $cells = [$appStructure->nameById(true, $positionInt)];
                    if ($positionId > 0) {
                        $excel = $excel->addRow([
                            ''
                        ]);
                    }
                }
            }

            $cells = array_merge($cells, $values);
            $excel = $excel->addRow($cells, $rowStyle);

            $positionId = $positionInt;
            $userId = $row->id;
        }

        $excel->toBrowser();
    }
}
