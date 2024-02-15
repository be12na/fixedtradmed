<?php

namespace App\Http\Controllers\Main\Settings;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $banks = app('neo')->mainBanks(false);

        return view('main.settings.bank.index', [
            'banks' => $banks,
            'windowTitle' => 'Pengaturan Bank Perusahaan',
            'breadcrumbs' => ['Pengaturan', 'Bank Perusahaan']
        ]);
    }

    public function validateInput(array $values, Bank $bank = null)
    {
        $inputBankCode = Arr::get($values, 'bank_code', '0');

        $uniqueAccountNo = new Unique('banks', 'account_no');
        $uniqueAccountNo = $uniqueAccountNo->where('bank_type', OWNER_BANK_MAIN)->where('bank_code', $inputBankCode);
        if (!empty($bank)) {
            $uniqueAccountNo = $uniqueAccountNo->ignore($bank->id, 'id');
        }

        $inBankCode = implode(',', array_keys(BANK_LIST));

        $validator = Validator::make($values, [
            'bank_code' => ['required', "in:{$inBankCode}"],
            'account_name' => ['required', 'string', 'max:100'],
            'account_no' => ['required', 'digits_between:8,20', $uniqueAccountNo],
        ], [], [
            'bank_code' => 'Bank',
            'account_name' => 'Nama Pemilik',
            'account_no' => 'No. Rekening',
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

    public function create(Request $request)
    {
        return view('main.settings.bank.form', [
            'data' => null,
            'postUrl' => route('main.settings.bank.store'),
            'modalHeader' => 'Tambah Bank Perusahaan',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.settings.bank.index');

        if ($valid['status'] === true) {
            try {
                $values['user_id'] = 0;
                $values['bank_type'] = OWNER_BANK_MAIN;
                $values['bank_name'] = Arr::get(BANK_LIST, $values['bank_code']);
                $values['active_at'] = date('Y-m-d H:i:s');
                $values['active_by'] = $request->user()->id;
                Bank::create($values);
                $valid['message'] = '';

                session([
                    'message' => 'Bank Perusahaan berhasil ditambahkan.',
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
        $data = $request->mainBank;

        return view('main.settings.bank.form', [
            'data' => $data,
            'postUrl' => route('main.settings.bank.update', ['mainBank' => $data->id]),
            'modalHeader' => 'Edit Bank Perusahaan',
        ]);
    }

    public function update(Request $request)
    {
        $bank = $request->mainBank;
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $bank);

        $responCode = 200;
        $responText = route('main.settings.bank.index');

        if ($valid['status'] === true) {
            try {
                $values['bank_name'] = Arr::get(BANK_LIST, $values['bank_code']);
                $bank->update($values);

                $valid['message'] = '';

                session([
                    'message' => 'Bank Perusahaan berhasil diubah.',
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
