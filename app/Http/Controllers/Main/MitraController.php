<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MitraPoint;
use App\Models\User;
use App\Models\UserBonus;
use App\Rules\NeoPassword;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Spatie\SimpleExcel\SimpleExcelWriter;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $currentBranchId = session('filter.branchId', -1);

        return view('main.mitra.index', [
            'currentBranchId' => $currentBranchId,
            'windowTitle' => 'Daftar Member',
            'breadcrumbs' => ['Member', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $userGroupMember = USER_GROUP_MEMBER;
        $userTypeMitra = USER_TYPE_MITRA;

        $queryReferral = DB::raw("
            (
                SELECT 0 as id, '-' as name, -1 as user_group, 0 as user_type, -1 as position_int
                UNION ALL
                SELECT id, name, user_group, user_type, position_int
                FROM users
                WHERE (user_group = {$userGroupMember}) AND (user_type in({$userTypeMitra}))
            ) as referral");

        // $queryPaket = DB::raw("(
        //     SELECT u.id as user_id, p.code as package_name
        //         FROM users u
        //         INNER JOIN mitra_purchases mp ON mp.mitra_id = u.id AND mp.purchase_status = 1
        //         INNER JOIN mitra_purchase_products mpp ON mpp.mitra_purchase_id = mp.id
        //         INNER JOIN products p ON p.id = mpp.product_id AND p.package_range = (
        //             SELECT MAX(p2.package_range) as p_range FROM products p2
        //             INNER JOIN mitra_purchase_products mpp2 ON mpp2.product_id = p2.id
        //             INNER JOIN mitra_purchases mp2 ON mp2.id = mpp2.mitra_purchase_id
        //             WHERE mp2.mitra_id = u.id AND mp2.purchase_status = 1
        //         )
        //     ) as paket");

        // paket.package_name
        $qryMitra = DB::table('users')
            ->selectRaw("
            users.id as mitra_id, users.username, users.name as mitra_name,
            users.email, users.phone, users.mitra_type,
            users.created_at, users.is_login, users.user_status, users.activated,
            CONCAT(DATE_FORMAT(users.created_at, '%Y-%m-%d'), '-', users.name) as tgl_register,
            referral.name as referral_name,
            referral.user_type as referral_type,
            referral.position_int as referral_position
            ")
            ->join($queryReferral, 'referral.id', '=', DB::raw('COALESCE(users.referral_id, 0)'))
            // ->leftJoin($queryPaket, 'paket.user_id', '=', 'users.id')
            ->where('users.activated', '=', true)
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_group', '=', $userGroupMember)
            ->where('users.user_type', '=', $userTypeMitra);

        $query = DB::table(DB::raw("({$qryMitra->toSql()}) as mitra"))->mergeBindings($qryMitra);

        return datatables()->query($query)
            ->editColumn('username', function ($row) {
                $format = '<div>%s</div><div class="fst-italic %s">%s</div>';
                $status = memberStatusText($row);
                $color = memberStatusColor($row);

                return new HtmlString(sprintf($format, $row->username, $color, $status));
            })
            ->editColumn('tgl_register', function ($row) {
                return formatFullDate($row->created_at);
            })
            // ->editColumn('package_name', function ($row) {
            //     return $row->package_name ?? 'Dropshipper';
            // })
            ->editColumn('referral_name', function ($row) {
                $result = "<div>{$row->referral_name}</div>";
                if ($row->referral_type != 0) {
                    $typeName = ($row->referral_type == 0) ? 'Perusahaan' : 'Member';
                    $result .= "<div class=\"text-decoration-underline fst-italic text-primary\">{$typeName}</div>";
                }

                return new HtmlString($result);
            })
            ->addColumn('view', function ($row) {
                $routeDetail = route('main.mitra.detail', ['userMitra' => $row->mitra_id]);
                $btnDetail = "<button type=\"button\" class=\"btn btn-sm btn-outline-primary\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDetail}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($btnDetail);
            })
            ->escapeColumns()
            ->toJson();
    }

    private function getStepForm(string $form, array $values, User $mitra, array $others = null)
    {
        $vars = [
            'values' => $values,
            'data' => $mitra,
        ];

        if (!empty($others)) $vars = array_merge($vars, $others);

        return view($form, $vars);
    }

    private function validateDataMitra(array $values, User $mitra)
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

    private function validateDataType(array $values, User $mitra)
    {
        $inTypes = implode(',', array_keys(MITRA_TYPES));
        $inLevels = implode(',', array_keys(MITRA_LEVELS));

        $validator = Validator::make($values, [
            'mitra_type' => ['required', 'integer', "in:{$inTypes}"],
        ], [], [
            'mitra_type' => 'Jenis',
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

    private function validateDataReferral(array &$values, User $member = null)
    {
        $referralExists = new Exists('users', 'id');

        $referralExists = $referralExists
            ->where('user_group', USER_GROUP_MEMBER)
            ->where('user_type', USER_TYPE_MEMBER)
            ->where('position_int', USER_INT_MGR);

        $validator = Validator::make($values, [
            'referral_id' => ['required', $referralExists]
        ], [
            'referral_id.required' => ':attribute harus dipilih.',
        ], [
            'referral_id' => 'Referral',
        ]);

        $valid = !$validator->fails();
        $message = $valid ? '' : $this->validationMessages($validator);

        $values['old_referral'] = $valid ? (optional($member->referral)->name ?? env('APP_COMPANY')) : null;
        $values['new_referral'] = $valid ? (optional(User::byId($values['referral_id'])->first())->name ?? 'Tidak Tersedia') : null;

        return (object) [
            'valid' => $valid,
            'message' => $message
        ];
    }

    public function detail(Request $request)
    {
        $mitra = $request->userMitra;

        return view('main.mitra.detail', [
            'data' => $mitra,
        ]);
    }

    public function edit(Request $request)
    {
        $mitra = $request->userMitra;

        return $this->getStepForm('main.mitra.forms.edit', [], $mitra);
    }

    public function update(Request $request)
    {
        $mitra = $request->userMitra;
        $values = $request->except(['_token', 'form_step']);
        $formStep = $request->get('form_step');

        if ($formStep != 'confirm') {

            if (!in_array($formStep, ['edit', 'block'])) {
                $fn = str_replace(' ', '', ucwords(str_replace('-', ' ', $formStep)));

                $fn = 'validateData' . $fn;
                $validation = $this->$fn($values, $mitra);

                if (!$validation->valid) {
                    return response($validation->message, 400);
                }
            }

            $nextStep = '';

            if (in_array($formStep, ['mitra', 'auth', 'type', 'referral', 'block'])) {
                $nextStep = 'confirm';
            } elseif ($formStep == 'edit') {
                $nextStep = $values['edit_choice'];
            }

            $others = [];

            if ($nextStep == 'mitra') {
                return $this->getStepForm('main.mitra.forms.data', $values, $mitra, $others);
            } elseif ($nextStep == 'type') {
                return $this->getStepForm('main.mitra.forms.type-level', $values, $mitra, $others);
            } elseif ($nextStep == 'auth') {
                return $this->getStepForm('main.mitra.forms.auth', $values, $mitra, $others);
            } elseif ($nextStep == 'referral') {
                $others = [
                    'managers' => User::query()->byBranchManager()->orderBy('name')->get(),
                ];
                return $this->getStepForm('main.mitra.forms.referral', $values, $mitra, $others);
            } elseif ($nextStep == 'confirm') {
                return $this->getStepForm('main.mitra.forms.confirm', $values, $mitra, $others);
            } elseif ($nextStep == 'block') {
                return $this->getStepForm('main.mitra.forms.block', $values, $mitra, $others);
            }

            return view('main.mitra.forms.unknown');
        }

        $responCode = 200;
        $responText = route("main.mitra.index");
        $message = 'Data Member berhasil diubah.';

        if ($values['edit_choice'] == 'auth') {
            if (!is_null($values['password']) && $values['password'] != '') {
                $values['password'] = Hash::make($values['password']);
            } else {
                unset($values['password']);
            }
            $message = 'Autentikasi Member berhasil diubah.';
        } elseif ($values['edit_choice'] == 'type') {
            $message = 'Jenis Member berhasil diubah.';
        } elseif ($values['edit_choice'] == 'referral') {
            $message = 'Referral Member berhasil diubah.';
        } elseif ($values['edit_choice'] == 'block') {
            $message = 'Member berhasil ' . ($mitra->is_login ? 'diblokir.' : 'diaktifkan kembali.');
            $values['is_login'] = !$mitra->is_login;
        }

        DB::beginTransaction();
        try {
            $mitra->timestamps = true;
            $mitra->update($values);

            session([
                'message' => $message,
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

    // download
    private function downloadQuery(Request $request)
    {
        $branchId = intval($request->get('branch_id', -1));

        $query = DB::table('users')
            ->selectRaw("
            users.id, users.name,
            users.email, users.phone, users.mitra_type,
            DATE_FORMAT(users.created_at, '%Y-%m-%d') as tgl_register,
            users.branch_id,
            branches.name as branch_name,
            referral.name as referral_name
            ")
            ->join('branches', 'branches.id', '=', 'users.branch_id')
            ->join(DB::raw('users as referral'), 'referral.id', '=', 'users.referral_id')
            ->where('users.activated', '=', true)
            ->where('users.user_status', '=', USER_STATUS_ACTIVE)
            ->where('users.user_group', '=', USER_GROUP_MEMBER)
            ->where('users.user_type', '=', USER_TYPE_MITRA)
            ->where('users.position_ext', '=', USER_EXT_MTR);

        if ($branchId != -1) {
            $query = $query->where('users.branch_id', '=', $branchId);
        }

        return (object) [
            'branchId' => $branchId,
            'rows' => $query
                ->orderBy('branches.name')
                ->orderBy(DB::raw("DATE_FORMAT(users.created_at, '%Y-%m-%d')"))
                ->orderBy('users.name')
                ->orderBy('referral.name')
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
        $branchName = '';
        if ($data->branchId > 0) {
            $branch = Branch::byId($data->branchId)->byActive()->first();
            if (empty($branch)) {
                return response(new HtmlString('<h1 style="color:red">Data Cabang Tidak ditemukan !!!</h1>'));
            }

            $branchName = $branch->name;
        }

        $cabangName = empty($branchName) ? '' : strtolower(str_replace(' ', '_', "-{$branchName}"));

        $downloadName = "Member{$cabangName}-{$tglName}.xlsx";
        $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->setFontName('tahoma')->build();
        $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setFontName('tahoma')->build();
        $rowStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->build();

        $excel = SimpleExcelWriter::streamDownload($downloadName)->noHeaderRow()
            ->addRow([
                '',
            ])
            ->addRow([
                'LAPORAN DATA MEMBER',
            ], $titleStyle)
            ->addRow([
                'Tanggal', ':', $tglReport,
            ], $titleStyle);

        if (!empty($branchName)) {
            $excel = $excel
                ->addRow([
                    'Cabang', ':', $branchName
                ], $titleStyle);
        }

        $printBranch = ($data->branchId <= 0);
        $branchId = 0;

        $headers = [];
        if ($printBranch) {
            $headers = ['Cabang'];
        }

        $headers = array_merge($headers, [
            'Tgl. Daftar', 'Nama', 'Jenis', 'Email', 'Handphone', 'Referral'
        ]);

        $excel = $excel
            ->addRow([
                '',
            ])
            ->addRow($headers, $headerStyle);

        foreach ($rows as $row) {
            $values = [
                $row->tgl_register,
                $row->name,
                Arr::get(MITRA_TYPES, $row->mitra_type, '-'),
                $row->email,
                $row->phone,
                $row->referral_name
            ];

            $branchInt = intval($row->branch_id);
            $cells = [];

            if ($printBranch) {
                $cells = [''];

                if ($branchId != $branchInt) {
                    $cells = [$row->branch_name ?? '-'];
                    if ($branchId > 0) {
                        $excel = $excel->addRow([
                            ''
                        ]);
                    }
                }
            }

            $cells = array_merge($cells, $values);
            $excel = $excel->addRow($cells, $rowStyle);

            $branchId = $branchInt;
        }

        $excel->toBrowser();
    }

    // ------------------ BARU REGISTER ------------------
    public function indexRegisterMitra(Request $request)
    {
        $branches = Branch::orderBy('name')->get();

        $currentBranchId = session('filter.branchId', -1);

        return view('main.mitra.register-index', [
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'windowTitle' => 'Daftar Member Baru',
            'breadcrumbs' => ['Member', 'Daftar Baru']
        ]);
    }

    public function registerDatatableMitra(Request $request)
    {
        $branchId = intval($request->get('branch_id', -1));

        $qryMitra = DB::table('users')
            ->join(DB::raw('users as referral'), 'referral.id', '=', 'users.referral_id')
            ->join('user_packages', function ($join) {
                return $join->on('user_packages.user_id', '=', 'users.id')
                    ->where('user_packages.status', '=', MITRA_PKG_TRANSFERRED);
            })
            ->selectRaw("
            users.id as mitra_id, users.username, users.name as mitra_name,
            users.email, users.phone,
            users.created_at,
            users.created_at as tgl_register,
            referral.name as referral_name,
            user_packages.package_id
            ")
            ->where('users.activated', '=', false)
            ->where('users.user_status', '=', USER_STATUS_INACTIVE)
            ->where('users.user_group', '=', USER_GROUP_MEMBER)
            ->where('users.user_type', '=', USER_TYPE_MITRA);

        session(['filter.branchId' => $branchId]);

        $query = DB::table(DB::raw("({$qryMitra->toSql()}) as mitra"))->mergeBindings($qryMitra);

        $result = datatables()->query($query)
            ->editColumn('tgl_register', function ($row) {
                return formatFullDate($row->created_at);
            })
            ->editColumn('package_id', function ($row) {
                return Arr::get(MITRA_TYPES, $row->package_id ?? 0, '-');
            })
            ->addColumn('view', function ($row) {
                $routeDetail = route('main.mitra.register.detail', ['registerMitra' => $row->mitra_id]);
                $btnDetail = "<button type=\"button\" class=\"btn btn-sm btn-outline-primary\" onclick=\"openDetailModal($(this));\" data-modal-url=\"{$routeDetail}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString($btnDetail);
            })->escapeColumns(['view']);

        return $result->toJson();
    }

    public function detailRegisterMitra(Request $request)
    {
        $mitra = $request->registerMitra;

        return view('main.mitra.register-detail', [
            'data' => $mitra,
            'postUrl' => route('main.mitra.register.action', ['registerMitra' => $mitra->id]),
            'modalHeader' => 'Detail Member Baru',
        ]);
    }

    public function actionRegisterMitra(Request $request)
    {
        $mitra = $request->registerMitra;

        $action_value = (string) $request->get('action_value');
        $reject_reason = $request->get('reject_reason');
        $responCode = 500;
        $responText = 'Unknown Command.';

        $valid = $validCommand = in_array($action_value, ['confirm', 'reject']);
        $isConfirm = ($action_value == 'confirm');

        if ($validCommand) {
            $inputs = $request->except(['_token']);
            $mitraType = implode(',', array_keys(MITRA_TYPES));

            $rules = [
                'mitra_type' => ['required', 'integer', "in:{$mitraType}"],
            ];

            if (!$isConfirm) {
                $rules[] = ['reject_reason' => 'required|string|max:250'];
            }

            $validator = Validator::make($inputs, $rules, [], [
                'reject_reason' => 'Alasan',
                'mitra_type' => 'Jenis',
            ]);

            $valid = !$validator->fails();

            if (!$valid) {
                $responCode = 400;
                $responText = view('partials.alert', [
                    'message' => $validator->errors()->first(),
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        if ($valid === true) {
            $responCode = 200;
            $responText = route('main.mitra.register.index');

            $time = time();
            $values = [];

            if ($action_value === 'confirm') {
                $values['user_status'] = USER_STATUS_ACTIVE;
                $values['status_at'] = $time;
                $values['activated'] = true;
                $values['activated_at'] = $time;
            } else {
                $values['status_at'] = $time;
                $values['status_logs'] = [
                    [
                        'text' => $reject_reason,
                        'at' => date('Y-m-d H:i:s', $time)
                    ]
                ];
            }

            $userPackage = $mitra->userPackage;

            $userPackageValues = [
                'status' => ($action_value === 'confirm') ? MITRA_PKG_CONFIRMED : MITRA_PKG_REJECTED,
                ($action_value === 'confirm') ? 'confirm_at' : 'reject_at' => date('Y-m-d H:i:s', $time),
            ];

            $bonusPoint = [];

            if (!$isConfirm) {
                $userPackageValues['note'] = $reject_reason;
            } else {
                $referral = $mitra->referral;

                if ($referral) {
                    $point = Arr::get(MITRA_POINTS, $mitra->mitra_type, 0);

                    if ($point > 0) {
                        $bonusPoint = [
                            'point_date' => $time,
                            'user_id' => $referral->id,
                            'from_user_id' => $mitra->id,
                            'point_type' => POINT_TYPE_ACTIVATE_MEMBER,
                            'user_package_id' => $userPackage->id,
                            'point_unit' => $point,
                            'point' => $point,
                        ];
                    }
                }
            }

            DB::beginTransaction();
            try {
                $mitra->timestamps = true;
                $mitra->update($values);

                $userPackage->update($userPackageValues);

                if ($isConfirm) {
                    if (!empty($bonusPoint)) {
                        MitraPoint::create($bonusPoint);
                    }

                    // UserBonus::createBonusSponsor($mitra);
                }

                $message = ($action_value === 'confirm')
                    ? 'Member berhasil dikonfirmasi.'
                    : 'Member berhasil ditolak.';

                session([
                    'message' => $message,
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                $responCode = 500;
                $message = 'Telah terjadi kesalahan pada server. Silahkan coba lagi';
                if (!isLive()) $message .= '. ' . $e->getMessage();

                $responText = view('partials.alert', [
                    'message' => $message,
                    'messageClass' => 'danger'
                ])->render();
            }
        } else {
            if ($responCode == 500) {
                $responText = view('partials.alert', [
                    'message' => $responText,
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }
}
