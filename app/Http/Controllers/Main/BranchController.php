<?php

namespace App\Http\Controllers\Main;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class BranchController extends Controller
{
    private Neo $neo;
    private bool $isAppV2;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->isAppV2 = true; //isAppV2();
    }

    public function index()
    {
        $branches = Branch::orderBy('name')->with(['distributors', 'zone'])->get();

        return view('main.branches.index', [
            'branches' => $branches,
            'isAppV2' => $this->isAppV2,
            'windowTitle' => 'Daftar Cabang',
            'breadcrumbs' => ['Kantor Cabang', 'Daftar']
        ]);
    }

    public function validateInput(array $values, Branch $branch = null)
    {
        $uniqueCode = new Unique('branches', 'code');
        $uniqueName = new Unique('branches', 'name');
        if (!empty($branch)) {
            $uniqueCode = $uniqueCode->ignore($branch->id, 'id');
            $uniqueName = $uniqueName->ignore($branch->id, 'id');
        }

        $zoneStr = implode(',', !$this->isAppV2 ? array_keys(BRANCH_ZONES) : $this->neo->zones(true)->pluck('id')->toArray());

        $validator = Validator::make($values, [
            'code' => ['required', 'string', 'max:20', $uniqueCode],
            'name' => ['required', 'string', 'max:100', $uniqueName],
            !$this->isAppV2 ? 'wilayah' : 'zone_id' => ['required', "in:{$zoneStr}"],
            'address' => ['nullable', 'string', 'max:250'],
            'pos_code' => ['nullable', 'digits_between:5,6'],
        ], [], [
            'code' => 'Kode',
            'name' => 'Nama Cabang',
            'wilayah' => 'Wilayah',
            'zone_id' => 'Zona',
            'address' => 'Alamat',
            'pos_code' => 'Kode Pos',
        ]);

        $result = ['status' => true, 'message' => ''];
        if ($validator->fails()) {
            $pesan = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
            foreach ($validator->errors()->toArray() as $errors) {
                $pesan .= '<li>' . $errors[0] . '</li>';
            }
            $pesan .= '</ul>';
            $result['status'] = false;
            $result['message'] = $pesan;
        }

        return $result;
    }

    public function create()
    {
        return view('main.branches.form', [
            'data' => null,
            'isAppV2' => $this->isAppV2,
            'postUrl' => route('main.branch.list.store'),
            'modalHeader' => 'Tambah Kantor Cabang',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.branch.list.index');

        if ($valid['status'] === true) {
            $values['is_active'] = (isset($values['is_active']) && ($values['is_active'] == 1));
            $values['active_at'] = date('Y-m-d H:i:s');
            $values['active_by'] = $request->user()->id;

            if (!isset($values['is_stock']) || !$values['is_active']) $values['is_stock'] = false;

            try {
                Branch::create($values);
                $valid['message'] = '';

                session([
                    'message' => 'Kantor Cabang berhasil ditambahkan.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi',
                    'messageClass' => 'danger'
                ])->render();
            }
        } else {
            $responCode = 400;
            $responText = view('partials.alert', [
                'message' => $valid['message'],
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        $branch = $request->branch;

        return view('main.branches.form', [
            'data' => $branch,
            'isAppV2' => $this->isAppV2,
            'postUrl' => route('main.branch.list.update', ['branch' => $branch->id]),
            'modalHeader' => 'Ubah Kantor Cabang',
        ]);
    }

    public function update(Request $request)
    {
        $branch = $request->branch;
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $branch);

        $responCode = 200;
        $responText = route('main.branch.list.index');

        if ($valid['status'] === true) {
            $oldActive = $branch->is_active;

            if (isset($values['is_active']) && ($values['is_active'] == 1)) {
                if (!$oldActive) {
                    $values['active_at'] = date('Y-m-d H:i:s');
                    $values['active_by'] = $request->user()->id;
                }

                if (!isset($values['is_stock'])) $values['is_stock'] = false;
            } else {
                if ($oldActive) {
                    $values['is_active'] = false;
                    $values['active_at'] = date('Y-m-d H:i:s');
                    $values['active_by'] = $request->user()->id;
                }

                $values['is_stock'] = false;
            }

            try {
                $branch->update($values);
                $valid['message'] = '';

                session([
                    'message' => 'Kantor Cabang berhasil diubah.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi',
                    'messageClass' => 'danger'
                ])->render();
            }
        } else {
            $responCode = 400;
            $responText = view('partials.alert', [
                'message' => $valid['message'],
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }
}
