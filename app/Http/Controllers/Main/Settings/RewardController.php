<?php

namespace App\Http\Controllers\Main\Settings;

use App\Http\Controllers\Controller;
use App\Models\MitraReward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class RewardController extends Controller
{
    public function index(Request $request)
    {
        return view('main.settings.reward.index', [
            'windowTitle' => 'Pengaturan Reward',
            'breadcrumbs' => ['Pengaturan', 'Reward']
        ]);
    }

    public function datatable(Request $request)
    {
        $canEdit = hasPermission('main.settings.reward.edit');

        return datatables()->eloquent(MitraReward::query()->byActive(true))
            ->editColumn('point', function ($row) {
                return formatNumber($row->point, 0);
            })
            ->addColumn('view', function ($row) use ($canEdit) {
                $buttons = [];

                if ($canEdit) {
                    $routeEdit = route('main.settings.reward.edit', ['mainReward' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeEdit}\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt\"></i></button>";
                }

                if (count($buttons) > 0) return new HtmlString(implode('', $buttons));

                return '';
            })
            ->escapeColumns()
            ->toJson();
    }

    private function validateInput(array $values, MitraReward $mitraReward = null): array
    {
        $result = ['status' => true, 'message' => ''];

        $uniquePoint = new Unique('mitra_points', 'point');

        if (!empty($mitraReward)) {
            $uniquePoint = $uniquePoint->ignore($mitraReward->id, 'id');
        }

        $validator = Validator::make($values, [
            'point' => ['required', 'integer', 'min:1', $uniquePoint],
            'reward' => ['required', 'string', 'max:100'],
        ], [], [
            'point' => 'Poin',
            'reward' => 'Reward',
        ]);

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

    public function create(Request $request)
    {
        return view('main.settings.reward.form', [
            'data' => null,
            'postUrl' => route('main.settings.reward.store'),
            'modalHeader' => 'Tambah Reward',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.settings.reward.index');

        if ($valid['status'] === true) {
            try {
                MitraReward::create($values);

                session([
                    'message' => 'Reward berhasil ditambahkan.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi ' . $e->getMessage(),
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
        $data = $request->mainReward;

        return view('main.settings.reward.form', [
            'data' => $data,
            'postUrl' => route('main.settings.reward.update', ['mainReward' => $data->id]),
            'modalHeader' => 'Edit Reward',
        ]);
    }

    public function update(Request $request)
    {
        $mitraReward = $request->mainReward;
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $mitraReward);

        $responCode = 200;
        $responText = route('main.settings.reward.index');

        if ($valid['status'] === true) {
            try {
                $mitraReward->update($values);

                session([
                    'message' => 'Reward berhasil diubah.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi ' . $e->getMessage(),
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
