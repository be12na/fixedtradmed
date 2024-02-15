<?php

namespace App\Http\Controllers\Member;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\BranchSale;
use App\Models\BranchTransfer;
use App\Models\BranchTransferDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class TransferController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $branches = $this->neo->myBranches($user);
        $dateRange = session('filter.dates', []);
        if (empty($dateRange)) {
            $date = Carbon::today();
            $dateRange = [
                'start' => (clone $date)->startOfWeek(),
                'end' => clone $date
            ];
        }

        $today = date('Y-m-d');

        if ($dateRange['start']->format('Y-m-d') > $today) {
            $dateRange['start'] = Carbon::today()->startOfWeek();
        }

        if ($dateRange['end']->format('Y-m-d') > $today) {
            $dateRange['end'] = Carbon::today();
        }

        $currentBranchId = session('filter.branchId', -1);
        $currentBankCode = session('filter.bankCode');
        $currentStatusId = session('filter.statusId', -1);

        return view('member.transfer.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentBankCode' => $currentBankCode,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Transfer',
            'breadcrumbs' => ['Transfer', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $user = $request->user();

        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromFormat('j F Y', $start_date);
        $endDate = Carbon::createFromFormat('j F Y', $end_date);

        if ($startDate->format('Ymd') > $endDate->format('Ymd')) {
            $tmpEnd = $startDate;
            $startDate = clone $endDate;
            $endDate = $tmpEnd;
        }

        $branchId = intval($request->get('branch_id', -1));
        $bankCode = $request->get('bank_code');
        $statusId = intval($request->get('status_id', -1));

        $inBranchIds = ($branchId == -1) ? $this->neo->myBranches($user)->pluck('id')->toArray() : [$branchId];

        $baseQuery = DB::table('branch_transfers')
            ->join('users', 'users.id', '=', 'branch_transfers.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_transfers.branch_id')
            ->selectRaw("
                branch_transfers.id,
                branch_transfers.code as kode,
                branch_transfers.transfer_date,
                concat(branch_transfers.transfer_date, '-', branch_transfers.id) as tanggal, 
                branch_transfers.bank_name,
                branch_transfers.transfer_status,
                branch_transfers.transfer_status as statusan,
                branch_transfers.total_omzets,
                branch_transfers.total_crews,
                branch_transfers.total_foundations,
                branch_transfers.total_savings,
                0 as total_bonus,
                branch_transfers.discount_amount,
                branch_transfers.omzet_used,
                branch_transfers.unique_digit,
                branch_transfers.total_transfer,
                users.name as manager_name,
                branches.name as branch_name
            ")
            ->whereBetween('branch_transfers.transfer_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereIn('branch_transfers.branch_id', $inBranchIds)
            ->where('branch_transfers.manager_id', '=', $user->id);

        if (!empty($bankCode)) {
            $baseQuery = $baseQuery->where('branch_transfers.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            $baseQuery = $baseQuery->where('branch_transfers.transfer_status', '=', $statusId);
        }

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
            'filter.bankCode' => $bankCode,
            'filter.statusId' => $statusId,
        ]);

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as transferan"))
            ->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->transfer_date);
            })
            ->editColumn('transfer_status', function ($row) {
                $cls = 'bg-warning';
                if ($row->transfer_status == PROCESS_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } elseif ($row->transfer_status == PROCESS_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                }

                $text = Arr::get(PROCESS_STATUS_LIST, $row->transfer_status);

                $html = "<div class=\"text-center p-1 {$cls}\">{$text}</div>";

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $routeView = route('member.transfer.detail', ['branchTransfer' => $row->id]);

                $buttonView = "<button type=\"button\" class=\"btn btn-sm btn-outline-success\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeView}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";
                return new HtmlString($buttonView);
            })->escapeColumns(['view']);

        return $result->toJson();
    }

    private function getSales(User $manager, $tanggal, $branchId)
    {
        $sales = BranchSale::byDate($tanggal)
            ->byBranch($branchId)
            ->byManager($manager)
            ->whereDoesntHave('transfer')
            ->with(['products', 'manager'])
            ->orderBy('id', 'desc')
            ->get();

        $saleItems = collect();
        foreach ($sales as $sale) {
            foreach ($sale->products as $saleItem) {
                $saleItems->push($saleItem);
            }
        }

        return [
            'sales' => $sales,
            'items' => $saleItems,
            'manager' => $manager
        ];
    }

    public function inputSummary(Request $request)
    {
        $user = $request->user();
        $tanggal = $request->get('tanggal');
        $branchId = $request->get('branch_id');

        if (empty($tanggal)) $tanggal = date('j F Y', strtotime('1970-01-01'));
        $tanggal = Carbon::createFromFormat('j F Y', resolveTranslatedDate($tanggal));

        $data = $this->getSales($user, $tanggal, $branchId);
        $managerName = optional($data['manager'])->name ?? '-';

        $result = [
            'total_sell' => $data['items']->sum('total_price'),
            'total_profit_crew' => $data['items']->sum('profit_crew'),
            'total_foundation' => $data['items']->sum('foundation'),
            'total_saving' => $data['sales']->sum('savings'),
            'potongan' => 0,
            'manager' => $managerName
        ];

        return response()->json($result);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $branches = $this->neo->myBranches($user);

        $today = Carbon::today();
        // $startWeek = (clone $today)->startOfWeek(0);
        // $selisih = $startWeek->diffInDays($today) + 7;
        $selisih = 30;

        $dateRange = [
            (clone $today)->subDays($selisih),
            $today
        ];

        $mainBanks = $this->neo->mainBanks(true);
        $cashName = Arr::get(BANK_TRANSFER_LIST, BANK_000);

        $banks = collect([
            (object) [
                'id' => 0,
                'bank_code' => BANK_000,
                'bank_name' => $cashName,
                'account_name' => $cashName,
                'account_no' => '000000000000',
                'upload' => 0,
            ]
        ]);

        foreach ($mainBanks as $mainBank) {
            $banks->push((object) [
                'id' => $mainBank->id,
                'bank_code' => $mainBank->bank_code,
                'bank_name' => $mainBank->bank_name,
                'account_name' => $mainBank->account_name,
                'account_no' => $mainBank->account_no,
                'upload' => 1,
            ]);
        }

        $banks = $banks->groupBy('bank_name');

        return view('member.transfer.form', [
            'branches' => $branches,
            'dateRange' => $dateRange,
            'banks' => $banks,
            'cashName' => $cashName,
            'windowTitle' => 'Tambah Transfer',
            'breadcrumbs' => ['Transfer', 'Tambah']
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $values['transfer_date'] = resolveTranslatedDate($values['transfer_date']);
        $saleDate = Carbon::createFromFormat('j F Y', $values['transfer_date']);

        $values['omzet_used'] = 0;

        $user = $request->user();
        $branches = $this->neo->myBranches($user);
        $branchIds = $branches->pluck('id')->toArray() ?? [mt_rand(10000000001, 99999999999)];

        $inBranchIds = implode(',', $branchIds);

        $mainBanks = $this->neo->mainBanks(true);
        $inBankIds = implode(',', array_merge([0], $mainBanks->pluck('id')->toArray()));

        $dataSale = $this->getSales($user, $saleDate, $values['branch_id']);
        $values['has_items'] = $dataSale['sales']->count();

        $mustUpload = ($values['bank_id'] > 0);
        $imageRules = [];
        if ($mustUpload) {
            $imageRules = ['image' => ['required', 'image', 'mimetypes:image/jpeg,image/png', 'max:512']];
        }

        $validator = Validator::make($values, array_merge([
            'transfer_date' => ['required', 'date_format:j F Y'],
            'branch_id' => ['required', "in:{$inBranchIds}"],
            'omzet_used' => ['required', 'integer', 'min:0'],
            'transfer_note' => ['nullable', 'string', 'max:250'],
            'bank_id' => ['required', "in:{$inBankIds}"],
            'has_items' => ['required', 'integer', 'min:1'],
        ], $imageRules), [
            'image.required' => ':attribute harus dilampirkan.',
            'has_items.min' => ':attribute tidak ditemukan.'
        ], [
            'transfer_date' => 'Tanggal',
            'branch_id' => 'Cabang',
            'omzet_used' => 'Penggunaan Omzet',
            'transfer_note' => 'Keterangan',
            'bank_id' => 'Bank',
            'image' => 'Bukti Transfer',
            'has_items' => 'Data Penjualan',
        ]);

        $responCode = 200;
        $responText = route('member.transfer.index');

        if ($validator->fails()) {
            $pesan = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
            foreach ($validator->errors()->toArray() as $errors) {
                $pesan .= '<li>' . $errors[0] . '</li>';
            }
            $pesan .= '</ul>';

            $responCode = 400;
            $responText = view('partials.alert', [
                'message' => $pesan,
                'messageClass' => 'danger'
            ])->render();
        } else {
            $values['transfer_date'] = $saleDate->format('Y-m-d');
            $values['code'] = BranchTransfer::makeCode($values['transfer_date']);
            $values['manager_id'] = $dataSale['manager']->id;
            $values['transfer_at'] = date('Y-m-d H:i:s');
            $values['transfer_status'] = PROCESS_STATUS_PENDING;

            if ($values['bank_id'] > 0) {
                $selectedBank = $mainBanks->where('id', '=', $values['bank_id'])->first();
                $values['bank_code'] = $selectedBank->bank_code;
                $values['bank_name'] = $selectedBank->bank_name;
                $values['account_no'] = $selectedBank->account_no;
                $values['account_name'] = $selectedBank->account_name;
            } elseif ($values['bank_id'] == 0) {
                $values['bank_code'] = BANK_000;
                $values['bank_name'] = Arr::get(BANK_TRANSFER_LIST, BANK_000);
                $values['account_no'] = BANK_000;
                $values['account_name'] = BANK_000;
            }

            $totalOmzet = $dataSale['items']->sum('total_price');
            $totalCrews = $dataSale['items']->sum('profit_crew');
            $totalFoundation = $dataSale['items']->sum('foundation');
            $totalSaving = $dataSale['sales']->sum('savings');
            // $subTotalSales = $totalOmzet - $totalCrews - $totalFoundation - $totalSaving;
            $subTotalSales = $totalOmzet - $totalSaving;

            $discountPersen = 0; // TODO: ???
            $discount = floor($discountPersen / 100 * $subTotalSales);

            $subTotal = $subTotalSales - $discount - $values['omzet_used'];

            $uniqueDigit = 0; // TODO: ???

            $totalTransfer = $subTotal + $uniqueDigit;

            $summaries = [
                'total_omzets' => $totalOmzet,
                'total_crews' => $totalCrews,
                'total_foundations' => $totalFoundation,
                'total_savings' => $totalSaving,
                'sub_total_sales' => $subTotalSales,
                'discount_persen' => $discountPersen,
                'discount_amount' => $discount,
                'sub_total' => $subTotal,
                'unique_digit' => $uniqueDigit,
                'total_transfer' => $totalTransfer,
            ];

            $values = array_merge($values, $summaries);
            $details = [];
            foreach ($dataSale['sales'] as $sale) {
                $saleItems = $sale->products;
                $details[] = [
                    'sale_id' => $sale->id,
                    'sale_total_price' => $saleItems->sum('total_price'),
                    'sale_total_crew' => $saleItems->sum('profit_crew'),
                    'sale_total_foundation' => $saleItems->sum('foundation'),
                    'sale_savings' => $sale->savings,
                ];
            }

            $isUploaded = false;
            $uploadedFile = null;

            DB::beginTransaction();
            try {
                if ($mustUpload) {
                    $file = $request->file('image');
                    $ext = $file->extension();
                    $filename = uniqid() . ".{$ext}";
                    $file->storePubliclyAs('/', $filename, BranchTransfer::IMAGE_DISK);
                    $values['image_transfer'] = $filename;

                    $isUploaded = true;
                    $uploadedFile = $filename;
                }

                if (isset($values['image'])) unset($values['image']);

                $transfer = BranchTransfer::create($values);

                foreach ($details as $detail) {
                    $detail['transfer_id'] = $transfer->id;

                    BranchTransferDetail::create($detail);
                }

                session([
                    'message' => 'Transfer Berhasil  di Simpan. Menunggu Admin Konfirmasi.',
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                if ($isUploaded && !empty($uploadedFile)) {
                    Storage::disk(BranchTransfer::IMAGE_DISK)->delete($uploadedFile);
                }

                $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }

    public function detail(Request $request)
    {
        return view('member.transfer.transfer-detail', [
            'transfer' => $request->branchTransfer
        ]);
    }
}
