<?php

namespace App\Http\Controllers\Mitra;

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
        $user = $request->user();
        $banks = $user->banks;

        return view('mitra.bank.index', [
            'banks' => $banks,
            'windowTitle' => 'Rekening Bank',
            'breadcrumbs' => ['Rekening Bank']
        ]);
    }

    public function validateInput(array $values, Bank $bank = null)
    {
        $inputBankCode = Arr::get($values, 'bank_code', '0');

        $uniqueAccountNo = new Unique('banks', 'account_no');
        $uniqueAccountNo = $uniqueAccountNo->where('bank_type', OWNER_BANK_MEMBER)->where('bank_code', $inputBankCode);
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
        return view('mitra.bank.form', [
            'data' => null,
            'postUrl' => route('mitra.bank.store'),
            'modalHeader' => 'Tambah Rekening Bank',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('mitra.bank.index');

        if ($valid['status'] === true) {
            $user = $request->user();

            try {
                $values['user_id'] = $user->id;
                $values['bank_type'] = OWNER_BANK_MEMBER;
                $values['bank_name'] = Arr::get(BANK_LIST, $values['bank_code']);
                $values['active_at'] = date('Y-m-d H:i:s');
                $values['active_by'] = $user->id;
                Bank::create($values);
                $valid['message'] = '';

                session([
                    'message' => 'Rekening Bank berhasil ditambahkan.',
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
        $data = $request->memberBank;

        return view('mitra.bank.form', [
            'data' => $data,
            'postUrl' => route('mitra.bank.update', ['memberBank' => $data->id]),
            'modalHeader' => 'Edit Rekening Bank',
        ]);
    }

    public function update(Request $request)
    {
        $bank = $request->memberBank;
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $bank);

        $responCode = 200;
        $responText = route('mitra.bank.index');

        if ($valid['status'] === true) {
            try {
                $values['bank_name'] = Arr::get(BANK_LIST, $values['bank_code']);
                $bank->update($values);

                $valid['message'] = '';

                session([
                    'message' => 'Rekening Bank berhasil diubah.',
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
