<?php

namespace App\Http\Controllers\Member;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchPayment;
use App\Models\BranchPaymentDetail;
use App\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class PaymentController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $branches = $this->neo->myBranches($user, true);

        $dateRange = session('filter.dates', []);
        if (empty($dateRange)) {
            $date = Carbon::today();
            $dateRange = [
                'start' => (clone $date)->startOfWeek(),
                'end' => (clone $date)->endOfWeek(DAY_SALE_ENDWEEK)
            ];
        }

        if ($dateRange['start']->format('Y-m-d') > date('Y-m-d')) {
            $dateRange['start'] = Carbon::today()->startOfWeek();
        }

        if ($dateRange['end']->format('Y-m-d') > date('Y-m-d')) {
            $dateRange['end'] = Carbon::today()->endOfWeek(DAY_SALE_ENDWEEK);
        }

        $currentBranchId = session('filter.branchId', -1);
        $currentBankCode = session('filter.bankCode');
        $currentStatusId = session('filter.paymentStatusId', -1);

        return view('member.payments.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentBankCode' => $currentBankCode,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Pembayaran',
            'breadcrumbs' => ['Pembayaran', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $user = $request->user();
        $isManager = ($user->is_branch_manager || ($user->position_int == USER_INT_MGR));

        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromFormat('j F Y', $start_date);
        $endDate = Carbon::createFromFormat('j F Y', $end_date);

        if ($startDate->format('Ymd') > $endDate->format('Ymd')) {
            $tmpEnd = $startDate;
            $startDate = clone $endDate;
            $endDate = $tmpEnd;
        }

        $branchId = $request->get('branch_id', -1);
        $bankCode = $request->get('bank_code');
        $statusId = intval($request->get('status_id', -1));

        $inBranchIds = ($branchId > 0) ? [$branchId] : $this->neo->myBranches($user, true)->pluck('id')->toArray();

        $filters = [
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
            'filter.bankCode' => $bankCode,
            'filter.paymentStatusId' => $statusId,
        ];

        $baseQuery = DB::table('branch_payments')
            ->join('users', 'users.id', '=', 'branch_payments.manager_id')
            ->join('branches', 'branches.id', '=', 'branch_payments.branch_id')
            ->selectRaw("
                branch_payments.id,
                branch_payments.code as kode,
                branch_payments.payment_date,
                branch_payments.total_price,
                branch_payments.total_discount,
                branch_payments.sub_total,
                branch_payments.unique_digit,
                branch_payments.total_transfer,
                branch_payments.transfer_status,
                concat(branch_payments.payment_date, '-', branch_payments.id) as tanggal, 
                users.name as manager_name,
                branches.name as branch_name
            ")
            ->whereBetween('branch_payments.payment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('branch_payments.deleted_at')
            ->whereIn('branch_payments.transfer_status', array_keys(PAYMENT_STATUS_LIST))
            ->where('branch_payments.manager_id', '=', $user->id)
            ->whereIn('branch_payments.branch_id', $inBranchIds);

        if (!empty($bankCode)) {
            $baseQuery = $baseQuery->where('branch_payments.bank_code', '=', $bankCode);
        }

        if (in_array($statusId, array_keys(PAYMENT_STATUS_LIST))) {
            $baseQuery = $baseQuery->where('branch_payments.transfer_status', '=', $statusId);
        }

        session($filters);

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as jualan"))
            ->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->payment_date);
            })
            ->editColumn('transfer_status', function ($row) {
                $cls = 'bg-light';
                if ($row->transfer_status == PAYMENT_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                } elseif ($row->transfer_status == PAYMENT_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } elseif ($row->transfer_status == PAYMENT_STATUS_TRANSFERRED) {
                    $cls = 'bg-warning';
                }

                $cls .= ' py-1 px-2';
                $statusName = Arr::get(PAYMENT_STATUS_LIST, $row->transfer_status);

                $html = "<span class=\"d-inline-block {$cls}\">{$statusName}</span>";

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $buttons = [];

                $routeView = route('member.payment.detail', ['branchPayment' => $row->id]);
                $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-info\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeView}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString(implode('', $buttons));
            })->escapeColumns();

        return $result->toJson();
    }

    private function getAvailableProducts($branchId, Carbon|string $datePayment = null, $mode = 'new'): Collection
    {
        $itemsProduct = collect();

        if (empty($branchId) || !in_array($mode, ['new', 'edit'])) return $itemsProduct;
        if (is_null($datePayment)) $datePayment = date('Y-m-d');

        $paymentDate = ($datePayment instanceof Carbon) ? $datePayment : Carbon::createFromTimestamp(strtotime($datePayment));

        if ($branchId instanceof Branch) {
            $branch = $branchId;
        } else {
            $branch = Branch::byId($branchId)->byActive()->first();
        }

        if (empty($branch)) return $itemsProduct;

        $branchProducts = $branch->stockProducts($paymentDate, true);
        if ($branchProducts->isEmpty()) return $itemsProduct;

        $productIds = $branchProducts->pluck('product_id')->toArray();
        $categories = ProductCategory::byActive()
            ->whereHas('products', function ($has) use ($productIds) {
                return $has->whereIn('id', $productIds);
            })
            ->with(['products' => function ($has) use ($productIds) {
                return $has->whereIn('id', $productIds)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $result = collect();

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $bProduct = $branchProducts->where('product_id', '=', $product->id)->first();
                if (empty($bProduct)) continue;

                $obj = (object) [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category' => $category,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product' => $product,
                    'stock' => $bProduct->selectedStock
                ];

                $result->push($obj);
            }
        }

        return $result->sortBy([['category_name', 'asc'], ['product_name', 'asc']]);
    }

    private function setItemsProduct($branchId, Carbon|string $paymentDate, $mode): Collection
    {
        $itemsProduct = collect();
        if (($stocks = $this->getAvailableProducts($branchId, $paymentDate, $mode))->isNotEmpty()) {
            foreach ($stocks as $stock) {
                $category = $itemsProduct->where('category_id', '=', $stock->category_id)->first();
                $product = $stock->product;
                if (empty($category)) {
                    $obj = (object) [
                        'category_id' => $stock->category_id,
                        'category' => $stock->category,
                        'products' => collect([$product]),
                    ];
                    $itemsProduct->push($obj);
                } else {
                    $category->products->push($product);
                }
            }
        }

        return $itemsProduct;
    }

    public function createItem(Request $request)
    {
        $responText = '';
        $responCode = 200;
        $branchId = $request->get('branch_id', 0);
        $mode = $request->get('mode');
        $paymentDate = Carbon::createFromFormat('Ymd', date('Ymd'));

        $itemsProduct = $this->setItemsProduct($branchId, $paymentDate, $mode);
        if ($itemsProduct->isNotEmpty()) {
            $responText = view('member.payments.payment-items', [
                'itemsProduct' => $itemsProduct,
                'paymentItem' => null,
            ])->render();
        }

        return response($responText, $responCode);
    }

    private function valuesAndValidator(Request $request): Response|array
    {
        $values = $request->except(['_token']);
        $user = $request->user();
        $branchId = $values['branch_id'];
        $branches = $this->neo->myBranches($user);
        $branchIds = $branches->pluck('id')->toArray() ?? [mt_rand(10000000001, 99999999999)];

        if (!in_array($branchId, $branchIds)) {
            $responText = view('partials.alert', [
                'message' => "Kantor cabang belum dipilih.",
                'messageClass' => 'danger'
            ])->render();

            return response($responText, 400);
        }

        $selectedBranch = $branches->where('id', '=', $branchId)->first();
        $values['branch'] = $selectedBranch;
        $values['payment_date'] = $datePayment = date('Y-m-d');
        $values['total_price'] = 0;
        $values['total_discount'] = 0;

        $stocks = $this->getAvailableProducts($selectedBranch, $values['payment_date'], 'new')
            ->whereIn('product_id', $values['product_ids']);

        $inBranchIds = implode(',', $branchIds);
        $inProductIds = implode(',', $stocks->pluck('product_id')->toArray() ?: [mt_rand(10000000001, 99999999999)]);

        $countProducts = count($values['product_ids']);
        $productIdsAlias = [];
        $productQtyAlias = [];
        $maxQtyRules = [];

        $values['product_quantities'] = [];
        $itemValues = [];

        $branchZoneId = $selectedBranch->zone_id;

        for ($i = 0; $i < $countProducts; $i++) {
            $available = $stocks->where('product_id', '=', $values['product_ids'][$i])->first();

            $product = $available->product;
            $stock = optional($available->stock);

            $productUnit = $product->satuan;
            $paymentUnit = $productUnit;
            $isNgecer = false;

            $fnPrice = 'zonePriceV2';
            $fnEceran = 'eceranZonePriceV2';

            $zonePrice = $isNgecer
                ? $product->$fnEceran($branchZoneId)
                : $product->$fnPrice($branchZoneId);

            $qty = $values['product_qty'][$i];
            $totalPrice = $zonePrice * $qty;

            $lastProduct = BranchPaymentDetail::query()
                ->where('product_id', '=', $product->id)
                ->orderBy('branch_payment_id', 'desc')
                ->first();

            $productDiscount = $product->mitraDiscount
                ->where('mitra_type', '=', MITRA_TYPE_AGENT)
                ->where('zone_id', '=', $branchZoneId)
                ->where('min_qty', '<=', $qty)
                ->sortBy([['min_qty', 'desc']])
                ->first();

            $discount = $productDiscount ? $productDiscount->discount : 0;
            $discountAmount = $discount * $qty;

            $itemValues[] = [
                'product_id' => $product->id,
                'product' => $product,
                'branch_product_id' => $stock->branch_product_id,
                'branch_stock_id' => $stock->id,
                'product_unit' => $paymentUnit,
                'product_zone' => $branchZoneId,
                'product_price' => $zonePrice,
                'product_qty' => $qty,
                'total_price' => $totalPrice,
                'discount_id' => optional($productDiscount)->id ?? 0,
                'product_discount' => $discount,
                'total_discount' => $discountAmount,
            ];

            $values['total_price'] += $totalPrice;
            $values['total_discount'] += $discountAmount;

            // for validation
            $lastQty = $lastProduct ? $lastProduct->product_qty : 0;
            $productMinQty = $product->mitraDiscount
                ->where('mitra_type', '=', MITRA_TYPE_AGENT)
                ->where('zone_id', '=', $branchZoneId)
                ->where('min_qty', '>', $lastQty)
                ->sortBy('min_qty')
                ->first();

            $minQty = $productMinQty ? $productMinQty->min_qty : $lastQty;

            $prodId = "product_ids.{$i}";
            $prodQty = "product_quantities.{$i}.value";
            $x = $i + 1;
            $title = "Product yang ke-{$x}";
            $productIdsAlias[$prodId] = $title;
            $productQtyAlias[$prodQty] = $title;

            $realStock = $stock->real_stock;
            $balance = 0;
            if (!empty($realStock)) {
                if ($productUnit == PRODUCT_UNIT_BOX) {
                    $balance = ($paymentUnit == PRODUCT_UNIT_PCS) ? $realStock->pcsBalance : $realStock->boxBalance;
                } else {
                    $balance = $realStock->pcsBalance;
                }
            }

            $maxQtyRules["product_quantities.{$i}.value"] = ['required', 'integer', "min:{$minQty}", "max:{$balance}"];
            $values['product_quantities'][$i] = [
                'value' => $qty,
            ];
        }

        $values['manager_id'] = $user->id;
        $values['sub_total'] = $values['total_price'] - $values['total_discount'];
        $values['unique_digit'] = 0;
        $values['total_transfer'] = $values['sub_total'] + $values['unique_digit'];
        $values['details'] = $itemValues;

        $validator = Validator::make($values, array_merge([
            'branch_id' => ['required', "in:{$inBranchIds}"],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', "in:{$inProductIds}", 'distinct'],
            'product_quantities' => ['required', 'array', "size:{$countProducts}"],
        ], $maxQtyRules), [
            'product_ids.*.distinct' => ':attribute tidak boleh ada yang sama.',
            'product_quantities.*.value.max' => 'Persediaan :attribute tidak mencukupi.',
        ], array_merge([
            'branch_id' => 'Cabang',
            'product_ids' => 'Produk',
            'product_quantities' => 'Jumlah Produk'
        ], $productIdsAlias, $productQtyAlias));

        return [
            'values' => $values,
            'validator' => $validator,
        ];
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $branches = $this->neo->myBranches($user, true);

        return view('member.payments.form', [
            'branches' => $branches,
            'windowTitle' => 'Tambah Pembelian',
            'breadcrumbs' => ['Pembelian', 'Tambah']
        ]);
    }

    private function validInputValues(Request $request): Response|array
    {
        $confirm = $this->valuesAndValidator($request);

        if ($confirm instanceof Response) return $confirm;

        if ($confirm['validator']->fails()) {
            return response($this->validationMessages($confirm['validator']), 400);
        }

        $values = $confirm['values'];

        unset($values['product_ids'], $values['product_qty'], $values['product_quantities']);

        return $values;
    }

    public function createConfirm(Request $request)
    {
        $values = $this->validInputValues($request);

        if ($values instanceof Response) return $values;

        return view('member.payments.confirm-form', [
            'values' => $values,
        ]);
    }

    public function store(Request $request)
    {
        $values = $this->validInputValues($request);

        if ($values instanceof Response) return $values;

        $values['code'] = BranchPayment::makeCode($values['payment_date']);

        $responCode = 200;
        $responText = route('member.payment.index');

        DB::beginTransaction();
        try {
            $branchPayment = BranchPayment::create($values);

            foreach ($values['details'] as $detail) {
                $detail['branch_payment_id'] = $branchPayment->id;

                BranchPaymentDetail::create($detail);
            }

            DB::commit();

            session([
                'message' => "Pembayaran berhasil ditambahkan.",
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }

    public function detail(Request $request)
    {
        $branchPayment = $request->branchPayment;

        return view('member.payments.detail', [
            'branchPayment' => $branchPayment
        ]);
    }

    public function detailTransfer(Request $request)
    {
        $branchPayment = $request->branchPaymentApproving;

        return view('member.payments.detail', [
            'branchPayment' => $branchPayment
        ]);
    }

    public function transfer(Request $request)
    {
        $branchPayment = $request->branchPaymentTransferable;
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

        return view('member.payments.transfer', [
            'branchPayment' => $branchPayment,
            'banks' => $banks,
            'cashName' => $cashName,
            'windowTitle' => 'Transfer Pembayaran',
            'breadcrumbs' => ['Pembayaran', 'Transfer']
        ]);
    }

    public function saveTransfer(Request $request)
    {
        $branchPayment = $request->branchPaymentTransferable;
        $values = $request->except(['_token']);

        $mainBanks = $this->neo->mainBanks(true);
        $inBankIds = implode(',', array_merge([0], $mainBanks->pluck('id')->toArray()));

        $mustUpload = ($values['bank_id'] > 0);
        $imageRules = [];
        if ($mustUpload) {
            $imageRules = ['image' => ['required', 'image', 'mimetypes:image/jpeg,image/png', 'max:512']];
        }

        $validator = Validator::make($values, array_merge([
            'transfer_note' => ['nullable', 'string', 'max:250'],
            'bank_id' => ['required', "in:{$inBankIds}"],
        ], $imageRules), [
            'image.required' => ':attribute harus dilampirkan.',
        ], [
            'transfer_note' => 'Keterangan',
            'bank_id' => 'Bank',
            'image' => 'Bukti Transfer',
        ]);

        $responCode = 200;
        $responText = route('member.payment.index');

        if ($validator->fails()) {
            $responCode = 400;
            $responText = $this->validationMessages($validator);
        } else {
            $values['transfer_status'] = PAYMENT_STATUS_TRANSFERRED;
            $values['transfer_at'] = date('Y-m-d H:i:s');

            if ($values['bank_id'] > 0) {
                $selectedBank = $mainBanks->where('id', '=', $values['bank_id'])->first();
                $values['bank_code'] = $selectedBank->bank_code;
                $values['bank_name'] = $selectedBank->bank_name;
                $values['account_no'] = $selectedBank->account_no;
                $values['account_name'] = $selectedBank->account_name;
            } else {
                $values['bank_code'] = BANK_000;
                $values['bank_name'] = Arr::get(BANK_TRANSFER_LIST, BANK_000);
                $values['account_no'] = BANK_000;
                $values['account_name'] = BANK_000;
            }

            $isUploaded = false;
            $uploadedFile = null;

            DB::beginTransaction();
            try {
                if ($mustUpload) {
                    $file = $request->file('image');
                    $ext = $file->extension();
                    $filename = uniqid() . ".{$ext}";
                    $file->storePubliclyAs('/', $filename, BranchPayment::IMAGE_DISK);
                    $values['image_transfer'] = $filename;

                    $isUploaded = true;
                    $uploadedFile = $filename;
                }

                if (isset($values['image'])) unset($values['image']);

                $branchPayment->update($values);

                session([
                    'message' => 'Transfer Berhasil  di Simpan. Menunggu Admin Konfirmasi.',
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                if ($isUploaded && !empty($uploadedFile)) {
                    Storage::disk(BranchPayment::IMAGE_DISK)->delete($uploadedFile);
                }

                $moreMessage = $this->neo->isLive() ? '' : $e->getMessage();

                $responCode = 500;
                $responText =  view('partials.alert', [
                    'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi. {$moreMessage}",
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }
}
