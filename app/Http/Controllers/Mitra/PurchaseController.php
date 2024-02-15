<?php

namespace App\Http\Controllers\Mitra;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\MitraPurchase;
use App\Models\MitraPurchaseProduct;
use App\Models\ProductCategory;
use App\Repositories\RegionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Exists;
use stdClass;

class PurchaseController extends Controller
{
    private Neo $neo;
    private bool $isAppV2;
    private float $specialDiscount;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->isAppV2 = isAppV2();
        $this->specialDiscount = 0;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $dateRange = session('filter.dates', []);
        $today = Carbon::today();

        if (empty($dateRange)) {
            $dateRange = [
                'start' => (clone $today)->startOfWeek(),
                'end' => $today
            ];
        }

        if ($dateRange['start']->format('Y-m-d') > date('Y-m-d')) {
            $dateRange['start'] = (clone $today)->startOfWeek();
        }

        if ($dateRange['end']->format('Y-m-d') > date('Y-m-d')) {
            $dateRange['end'] = $today;
        }

        $currentStatusId = session('filter.statusId', PROCESS_STATUS_PENDING);
        $currentBranchId = session('filter.branchId', $user->branch_id ?? -1);
        $branches = Branch::whereHas('manager')->orderBy('name')->get();

        return view('mitra.purchase.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentStatusId' => $currentStatusId,
            'windowTitle' => 'Daftar Pembelian',
            'breadcrumbs' => ['Pembelian', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $user = $request->user();

        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromFormat('j F Y', $start_date);
        $endDate = Carbon::createFromFormat('j F Y', $end_date);
        $statusId = intval($request->get('status_id', -1));

        $filters = [
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.statusId' => $statusId,
        ];

        $pendingName = Arr::get(PROCESS_STATUS_LIST, PROCESS_STATUS_PENDING);
        $approveName = Arr::get(PROCESS_STATUS_LIST, PROCESS_STATUS_APPROVED);
        $rejectName = Arr::get(PROCESS_STATUS_LIST, PROCESS_STATUS_REJECTED);
        $PROCESS_STATUS_PENDING = PROCESS_STATUS_PENDING;
        $PROCESS_STATUS_APPROVED = PROCESS_STATUS_APPROVED;
        $PROCESS_STATUS_REJECTED = PROCESS_STATUS_REJECTED;

        // $baseQuery = DB::table('mitra_purchases')
        //     ->join('mitra_purchase_products', 'mitra_purchase_products.mitra_purchase_id', '=', 'mitra_purchases.id')
        //     ->join('products', 'products.id', '=', 'mitra_purchase_products.product_id')
        //     ->selectRaw("
        //         mitra_purchases.id,
        //         mitra_purchases.code as kode,
        //         mitra_purchases.purchase_date,
        //         concat(mitra_purchases.purchase_date, '-', mitra_purchases.id) as tanggal,
        //         mitra_purchases.mitra_note,
        //         mitra_purchases.admin_note,
        //         mitra_purchases.is_transfer,
        //         mitra_purchases.total_purchase,
        //         mitra_purchases.discount_amount,
        //         mitra_purchases.total_transfer,
        //         mitra_purchases.purchase_status,
        //         (CASE WHEN mitra_purchases.purchase_status = {$PROCESS_STATUS_PENDING} THEN
        //             CASE WHEN mitra_purchases.is_transfer = 1 THEN 'Menunggu Konfirmasi' ELSE 'Transfer' END
        //         WHEN mitra_purchases.purchase_status = {$PROCESS_STATUS_APPROVED} THEN '{$approveName}'
        //         WHEN mitra_purchases.purchase_status = {$PROCESS_STATUS_REJECTED} THEN '{$rejectName}'
        //         ELSE '{$pendingName}'
        //         END) as status_name,
        //         null as purchase_items,
        //         GROUP_CONCAT(products.name) as product_names,
        //         GROUP_CONCAT(mitra_purchase_products.product_qty) as product_quantities,
        //         GROUP_CONCAT(mitra_purchase_products.product_price) as product_prices,
        //         GROUP_CONCAT(mitra_purchase_products.total_price) as total_product_prices
        //     ")
        //     ->where('mitra_purchases.mitra_id', '=', $user->id)
        //     ->whereBetween('mitra_purchases.purchase_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
        //     ->whereNull('mitra_purchases.deleted_at')
        //     ->where('mitra_purchases.is_active', '=', true)
        //     ->groupBy([
        //         'mitra_purchases.id',
        //         'mitra_purchases.code',
        //         'mitra_purchases.purchase_date',
        //         'mitra_purchases.mitra_note',
        //         'mitra_purchases.admin_note',
        //         'mitra_purchases.is_transfer',
        //         'mitra_purchases.total_purchase',
        //         'mitra_purchases.discount_amount',
        //         'mitra_purchases.total_transfer',
        //         'mitra_purchases.purchase_status',
        //     ]);

        // if (in_array($statusId, [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
        //     $baseQuery = $baseQuery->where('mitra_purchases.purchase_status', '=', $statusId);
        // }

        // $query = DB::table(DB::raw("({$baseQuery->toSql()}) as jualan"))
        //     ->mergeBindings($baseQuery);

        session($filters);

        $query = MitraPurchase::query()
            ->byMitra($user)
            ->byBetweenDate($startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

        $inStatus = [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED];

        if (in_array($statusId, $inStatus)) {
            $query = $query->byStatus($statusId);
        } else {
            $query = $query->byStatus($inStatus);
        }

        $query = $query->with('products');

        // $result = datatables()->query($query)
        $result = datatables()->eloquent($query)
            // ->editColumn('tanggal', function ($row) {
            //     return formatFullDate($row->purchase_date);
            // })
            // ->editColumn('purchase_date', function ($row) {
            //     return formatFullDate($row->purchase_date);
            // })
            // ->editColumn('status_name', function ($row) {
            //     $cls = 'bg-light';
            //     if ($row->purchase_status == PROCESS_STATUS_REJECTED) {
            //         $cls = 'bg-danger text-light';
            //     } elseif ($row->purchase_status == PROCESS_STATUS_APPROVED) {
            //         $cls = 'bg-success text-light';
            //     } else {
            //         if ($row->is_transfer != 1) {
            //             $cls = 'bg-warning';
            //         }
            //     }

            //     $cls .= ' py-1 px-2';

            //     $html = "<span class=\"d-inline-block {$cls}\">{$row->status_name}</span>";

            //     return new HtmlString($html);
            // })
            ->editColumn('purchase_status', function ($row) {
                $cls = 'bg-light';
                if ($row->purchase_status == PROCESS_STATUS_REJECTED) {
                    $cls = 'bg-danger text-light';
                } elseif ($row->purchase_status == PROCESS_STATUS_APPROVED) {
                    $cls = 'bg-success text-light';
                } else {
                    if ($row->is_transfer != 1) {
                        $cls = 'bg-warning';
                    }
                }

                $cls .= ' py-1 px-2';

                $html = "<span class=\"d-inline-block {$cls}\">{$row->status_text}</span>";

                return new HtmlString($html);
            })
            ->addColumn('purchase_items', function ($row) {
                $html = '';
                $itemsName = $row->products->pluck('product.name');
                $itemsQty = $row->products->pluck('product_qty');

                for ($i = 0; $i < count($itemsName); $i++) {
                    $name = $itemsName[$i];
                    $qty = formatNumber($itemsQty[$i]);

                    $html .= "<div class=\"d-flex flex-nowrap justify-content-between\"><span class=\"me-2\">- {$name}</span><span class=\"ms-2\">{$qty}</span></div>";
                }

                return new HtmlString($html);
            })
            ->addColumn('view', function ($row) {
                $buttons = [];

                if (!$row->is_transfer || ($row->is_transfer && ($row->purchase_status == PROCESS_STATUS_REJECTED))) {
                    $routeTransfer = route('mitra.purchase.transfer.index', ['mitraPurchase' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-primary me-1\" onclick=\"window.location.href='{$routeTransfer}';\" title=\"Transfer\"><i class=\"fa-solid fa-money-bill-transfer\"></i></button>";
                }

                return new HtmlString(implode('', $buttons));
            })->escapeColumns();

        return $result->toJson();
    }

    private function getAvailableProducts($mode = 'new'): stdClass
    {
        $result = (object) [
            'selectedBranch' => null,
            'items' => collect(),
        ];

        if (!in_array($mode, ['new', 'edit'])) return $result;

        $categories = ProductCategory::byActive()
            ->whereHas('products', function ($has) {
                return $has->byPublished(true)->byHasPrice('harga_a');
            })
            ->with(['products' => function ($has) {
                return $has->byPublished(true)->byHasPrice('harga_a');
            }])
            ->orderBy('name')
            ->get();

        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $obj = (object) [
                    'category_id' => $category->id,
                    'category_name' => $category->name,
                    'category' => $category,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product' => $product,
                ];

                $result->items->push($obj);
            }
        }

        return $result;
    }

    private function setItemsProduct($mode = 'new'): Collection
    {
        $itemsProduct = collect();
        $available = $this->getAvailableProducts($mode);
        foreach ($available->items as $item) {
            $category = $itemsProduct->where('category_id', '=', $item->category_id)->first();
            $product = $item->product;
            if (empty($category)) {
                $obj = (object) [
                    'category_id' => $item->category_id,
                    'category' => $item->category,
                    'products' => collect([$product]),
                ];
                $itemsProduct->push($obj);
            } else {
                $category->products->push($product);
            }
        }

        return $itemsProduct;
    }

    public function createItem(Request $request)
    {
        $responText = '';
        $responCode = 200;
        $mode = $request->get('mode');
        $itemsProduct = $this->setItemsProduct($mode);

        if ($itemsProduct->isNotEmpty()) {
            $responText = view('mitra.purchase.form-items', [
                'itemsProduct' => $itemsProduct,
                'purchaseItem' => null,
            ])->render();
        }

        return response($responText, $responCode);
    }

    public function productQty(Request $request)
    {
        $productId = $request->get('product_id', 0);
        $available = $this->getAvailableProducts();

        $product = $available->items
            ->pluck('product')
            ->values()
            ->where('id', '=', $productId)
            ->first();

        $content = '';

        if (!empty($product) && !empty($branch)) {
            $zoneId = $branch->zone_id;

            $normalPrice = $product->zoneMitraPriceV2($zoneId);
            $promoPrice = $product->zoneMitraPromoPriceV2($zoneId);

            $content = '<input type="number" class="form-control sale-item-qty" min="1" step="1" name="product_qty[]" value="1" data-normal-price="%s" data-promo-price="%s" data-special-discount="%s" data-discount="%s" autocomplete="off">';

            $discounts = $product->mitraDiscount->where('zone_id', '=', $zoneId)->sortBy('min_qty')->values()->pluck('discount', 'min_qty')->toArray();

            $arrDiscounts = [];
            foreach ($discounts as $minQty => $discount) {
                $arrDiscounts[] = "{$minQty}|{$discount}";
            }

            $content = sprintf($content, $normalPrice, $promoPrice, $this->specialDiscount, implode(',', $arrDiscounts));
        }

        return new HtmlString($content);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $isPremium = ($user->mitra_type == MITRA_TYPE_AGENT);
        $branch = Branch::where('id', '=', BRANCH_CENTRAL)
            ->with('zone')
            ->first();

        $today = Carbon::today();
        $dateRange = [
            (clone $today)->subDays(30),
            $today
        ];

        $branches = Branch::byActive()
            ->byCanStock(true)
            ->whereHas('distributors')
            ->whereHas('zone')
            ->orderBy('name')
            ->get();

        return view('mitra.purchase.form', [
            'branch' => $branch,
            'branches' => $branches,
            'dateRange' => $dateRange,
            'isMitraPremium' => $isPremium,
            'foundationPersen' => SALE_FOUNDATION_PERSEN,
            'specialDiscount' => $this->specialDiscount,
            'windowTitle' => 'Tambah Pembelian',
            'breadcrumbs' => ['Pembelian', 'Tambah']
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $values = [
            'purchase_date' => $request->get('purchase_date'),
            'branch_id' => 0,
            'mitra_id' => $user->id,
            'mitra_type_id' => $user->mitra_type,
            'customer_identity' => $request->get('customer_identity'),
            'customer_name' => $request->get('customer_name'),
            'customer_address' => $request->get('customer_address'),
            'customer_village_id' => $request->get('customer_village_id'),
            'customer_pos_code' => $request->get('customer_pos_code'),
            'customer_phone' => $request->get('customer_phone'),
            'product_ids' => $request->get('product_ids', []),
            'product_qty' => $request->get('product_qty', []),
            'savings' => $request->get('savings', 0),
            'mitra_note' => $request->get('mitra_note'),
            'is_v2' => false,
        ];

        if (!empty($values['purchase_date'])) {
            $values['purchase_date'] = resolveTranslatedDate($values['purchase_date'], ' ');
        }

        $availableProducts = $this->getAvailableProducts('new');
        $stocks = $availableProducts->items->whereIn('product_id', $values['product_ids']);

        $values['manager_id'] = 0;
        $values['zone_id'] = ZONE_WEST;
        $values['referral_id'] = $user->referral_id;
        $values['total_zone_discount'] = 0;
        $values['total_coupon_discount'] = 0;

        $inProductIds = implode(',', $stocks->pluck('product_id')->toArray() ?: [mt_rand(10000000001, 99999999999)]);

        $countProducts = count($values['product_ids']);
        $productIdsAlias = [];
        $productQtyAlias = [];
        $maxQtyRules = [];

        $values['product_quantities'] = [];
        $itemValues = [];
        $totalPurchase = 0;
        $persenBonus =  0;

        for ($i = 0; $i < $countProducts; $i++) {
            $available = $stocks->where('product_id', '=', $values['product_ids'][$i])->first();
            $product = $available->product;
            $normalZonePrice = $product->harga_a;
            $promoZonePrice = $normalZonePrice;

            $zonePrice = ($promoZonePrice > 0) ? $promoZonePrice : $normalZonePrice;

            $qty = $values['product_qty'][$i];

            $totalPrice = $zonePrice * $qty;
            $persenCrew = 0;
            $profitCrew = floor($persenCrew * $totalPrice / 100);
            $foundationPersen = 0;
            $foundation = floor($foundationPersen * $profitCrew / 100);
            $totalProfit = $totalPrice - $profitCrew - $foundation;

            $discount = 0;
            $discountId = null;

            $mitraDiscount = $product->mitraDiscount()
                ->where('min_qty', '<=', $qty)
                ->orderBy('min_qty', 'desc')
                ->first();

            if (!empty($mitraDiscount)) {
                $discountId = $mitraDiscount->id;
                $discount = $mitraDiscount->discount;
            }

            $zoneDiscount = $discount * $qty;
            $totalPrice = $totalPrice - $zoneDiscount;
            $percentCoupon = $this->specialDiscount;
            $couponDiscount = intval(floor($percentCoupon / 100 * $totalPrice));
            $totalPrice = $totalPrice - $couponDiscount;

            $totalPurchase += $totalPrice;

            $values['total_zone_discount'] += $zoneDiscount;
            $values['total_coupon_discount'] += $couponDiscount;

            $itemValues[] = [
                'product_id' => $product->id,
                'branch_product_id' => 0,
                'branch_stock_id' => 0,
                'product_unit' => $product->satuan,
                'product_zone' => ZONE_WEST,
                'product_price' => $normalZonePrice,

                'product_zone_id' => 0,
                'is_promo' => ($promoZonePrice > 0),
                'product_zone_price' => $zonePrice,

                'product_qty' => $qty,
                'total_price' => $totalPrice,
                'persen_mitra' => $persenBonus,
                'profit_mitra' => $profitCrew,
                'persen_foundation' => $foundationPersen,
                'foundation' => $foundation,

                'discount_id' => $discountId,
                'discount' => $discount,
                'coupon_id' => 0,
                'coupon_is_percent' => true,
                'coupon_percent' => $percentCoupon,
                'coupon_discount' => $couponDiscount,

                'total_profit' => $totalProfit,
                'is_v2' => false,
            ];

            // for validation
            $prodId = "product_ids.{$i}";
            $prodQty = "product_quantities.{$i}.value";
            $x = $i + 1;
            $title = "Product yang ke-{$x}";
            $productIdsAlias[$prodId] = $title;
            $productQtyAlias[$prodQty] = $title;
            $maxQtyRules["product_quantities.{$i}.value"] = ['required', 'integer', 'min:1'];
            $values['product_quantities'][$i] = [
                'value' => $qty,
            ];
        }

        $values['delivery_fee'] = 0;
        $values['total_purchase'] = $totalPurchase;
        $values['total_transfer'] = $values['total_purchase'] + $values['delivery_fee'];

        $validator = Validator::make(
            $values,
            array_merge([
                'purchase_date' => ['required', 'date_format:j F Y'],
                'customer_identity' => ['nullable', 'string', 'max:50'],
                'customer_name' => ['required', 'string', 'max:100'],
                'customer_address' => ['required', 'string', 'max:250'],
                'customer_village_id' => ['required', 'string', new Exists('villages', 'id')],
                'customer_pos_code' => ['nullable', 'digits_between:5,6'],
                'customer_phone' => ['nullable', 'digits_between:8,15', 'starts_with:08'],
                'product_ids' => ['required', 'array', 'min:1'],
                'product_ids.*' => ['required', "in:{$inProductIds}", 'distinct'],
                'product_quantities' => ['required', 'array', "size:{$countProducts}"],
                'mitra_note' => ['nullable', 'string', 'max:250'],
            ], $maxQtyRules),
            [
                'manager_id.required' => 'Cabang tersebut tidak memiliki manager untuk melakukan pengelolaan pesanan.',
                'product_ids.*.distinct' => ':attribute tidak boleh ada yang sama.',
                'product_quantities.*.value.max' => 'Persediaan :attribute tidak mencukupi.',
            ],
            array_merge([
                'purchase_date' => 'Tanggal',
                'branch_id' => 'Cabang',
                'customer_identity' => 'No. Identitas Customer',
                'customer_name' => 'Nama Customer',
                'customer_address' => 'Alamat Customer',
                'customer_village_id' => 'Daerah Customer',
                'customer_pos_code' => 'Kode Pos Customer',
                'customer_phone' => 'No. Handphone Customer',
                'product_ids' => 'Produk',
                'product_quantities' => 'Jumlah Produk',
                'mitra_note' => 'Catatan',
            ], $productIdsAlias, $productQtyAlias)
        );

        $responCode = 200;
        $responText = route('mitra.purchase.create');

        if ($validator->fails()) {
            $responCode = 400;
            $responText = $this->validationMessages($validator);
        } else {
            $okRegion = true;

            $village = RegionRepository::getVillageById($values['customer_village_id'], ['district', 'city', 'province']);
            if (empty($village)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Desa / Kelurahan Customer tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $district = $okRegion ? $village->district : null;
            if (empty($district)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Kecamatan Customer tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $city = $okRegion ? $district->city : null;
            if (empty($city)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Kota / Kabupaten Customer tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            $province = $okRegion ? $city->province : null;
            if (empty($city)) {
                $responCode = 404;
                $responText = view('partials.alert', [
                    'message' => 'Propinsi Customer tidak ditemukan',
                    'messageClass' => 'danger'
                ])->render();

                $okRegion = false;
            }

            if ($okRegion === true) {
                $values['purchase_date'] = strtotime($values['purchase_date']);
                $values['code'] = MitraPurchase::makeCode($values['purchase_date']);
                $values['is_delivery'] = true;
                $values['customer_province_id'] = $province->id;
                $values['customer_province'] = $province->name;
                $values['customer_city_id'] = $city->id;
                $values['customer_city'] = $city->name;
                $values['customer_district_id'] = $district->id;
                $values['customer_district'] = $district->name;
                $values['customer_village_id'] = $village->id;
                $values['customer_village'] = $village->name;

                unset($values['product_ids']);
                unset($values['product_qty']);
                unset($values['product_quantities']);

                DB::beginTransaction();
                try {
                    $purchase = MitraPurchase::create($values);
                    foreach ($itemValues as $item) {
                        $item['mitra_purchase_id'] = $purchase->id;
                        MitraPurchaseProduct::create($item);
                    }

                    DB::commit();

                    $responText = route('mitra.purchase.transfer.index', ['mitraPurchase' => $purchase->id]);

                    session([
                        'message' => "Pembelian berhasil ditambahkan.<br>Silahkan Hubungi Admin Untuk Konfirmasi Pemesanan di Nomor Whatsapp: 081229194684 atau Klik <a href=\"https://wa.me/6281229194684\">DISINI</a>",
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
            }
        }

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        //
    }

    public function update(Request $request)
    {
        //
    }

    public function remove(Request $request)
    {
        //
    }

    public function destroy(Request $request)
    {
        //
    }

    public function transfer(Request $request)
    {
        $purchase = $request->mitraPurchase;

        if ($purchase->is_transfer && ($purchase->purchase_status != PROCESS_STATUS_REJECTED)) {
            return redirect()->route('mitra.purchase.index')
                ->with('message', 'Data tidak ditemukan.')
                ->with('messageClass', 'danger');
        }

        $mainBanks = $this->neo->mainBanks(true);
        $cashName = Arr::get(BANK_TRANSFER_LIST, BANK_000);

        // $banks = collect([
        //     (object) [
        //         'id' => 0,
        //         'bank_code' => BANK_000,
        //         'bank_name' => $cashName,
        //         'account_name' => $cashName,
        //         'account_no' => '000000000000',
        //         'upload' => 0,
        //     ]
        // ]);

        $banks = collect();

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

        return view('mitra.purchase.transfer', [
            'banks' => $banks,
            'purchase' => $purchase,
            'cashName' => $cashName,
            'windowTitle' => 'Tambah Transfer',
            'breadcrumbs' => ['Transfer', 'Tambah']
        ]);
    }

    public function saveTransfer(Request $request)
    {
        $purchase = $request->mitraPurchase;
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
            'transfer_note' => 'Keterangan Transfer',
            'bank_id' => 'Bank',
            'image' => 'Bukti Transfer',
        ]);

        $responCode = 200;
        $responText = route('mitra.purchase.index');

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
            $values['is_transfer'] = true;
            $values['transfer_at'] = time();

            if ($purchase->purchase_status == PROCESS_STATUS_REJECTED) {
                $values['purchase_status'] = PROCESS_STATUS_PENDING;
            }

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

            $isUploaded = false;
            $uploadedFile = null;
            $oldFile = $purchase->image_transfer;

            DB::beginTransaction();
            try {
                if ($mustUpload) {
                    $file = $request->file('image');
                    $ext = $file->extension();
                    $filename = "mitra-purchase-{$purchase->id}" . uniqid() . ".{$ext}";
                    $file->storePubliclyAs('/', $filename, MitraPurchase::IMAGE_DISK);
                    $values['image_transfer'] = $filename;

                    $isUploaded = true;
                    $uploadedFile = $filename;
                }

                if (isset($values['image'])) unset($values['image']);

                $purchase->update($values);

                if ($isUploaded && !empty($oldFile)) {
                    Storage::disk(MitraPurchase::IMAGE_DISK)->delete($oldFile);
                }

                session([
                    'message' => 'Upload bukti transfer berhasil.',
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                if ($isUploaded && !empty($uploadedFile)) {
                    Storage::disk(MitraPurchase::IMAGE_DISK)->delete($uploadedFile);
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
}
