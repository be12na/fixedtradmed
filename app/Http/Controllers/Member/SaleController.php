<?php

namespace App\Http\Controllers\Member;

use App\Helpers\AppStructure;
use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSale;
use App\Models\BranchSalesProduct;
use App\Models\ProductCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;

class SaleController extends Controller
{
    private Neo $neo;
    private AppStructure $appStructure;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->appStructure = app('appStructure');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $branches = collect();
        $isManager = ($user->is_branch_manager || ($user->position_int == USER_INT_MGR));

        if ($isManager) {
            $branches = $this->neo->myBranches($user);
        }

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
        $currentSalesmanId = session('filter.salesmanId');
        $currentSalesman = ($currentSalesmanId && ($currentSalesmanId > 0)) ? User::byId($currentSalesmanId)->first() : null;

        return view('member.sales.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'isManager' => $isManager,
            'currentBranchId' => $currentBranchId,
            'currentSalesman' => $currentSalesman,
            'windowTitle' => 'Daftar Penjualan',
            'breadcrumbs' => ['Penjualan', 'Daftar']
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

        $strWhereTransfer = implode(',', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
        $transferQuery = DB::table('branch_transfers')
            ->join('branch_transfer_details', 'branch_transfer_details.transfer_id', '=', 'branch_transfers.id')
            ->selectRaw('
                branch_transfer_details.sale_id,
                branch_transfer_details.transfer_id,
                COUNT(1) as temp_count
            ')
            ->whereRaw("(branch_transfers.deleted_at is null) AND (branch_transfers.transfer_status in($strWhereTransfer))")
            ->groupBy(['branch_transfer_details.sale_id', 'branch_transfer_details.transfer_id'])
            ->toSql();

        $filters = [
            'filter.dates' => ['start' => $startDate, 'end' => $endDate]
        ];

        $baseQuery = DB::table('branch_sales')
            ->join('users', 'users.id', '=', 'branch_sales.salesman_id')
            ->join(DB::raw('users as manager'), 'manager.id', '=', 'branch_sales.manager_id')
            ->join('branch_sales_products', 'branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
            ->join('branches', 'branches.id', '=', 'branch_sales.branch_id')
            ->leftJoin(DB::raw("({$transferQuery}) as transferan"), function ($join) {
                $join->on('transferan.sale_id', '=', 'branch_sales.id');
            })
            ->selectRaw("
                branch_sales.id,
                branch_sales.code as kode,
                branch_sales.sale_date,
                concat(branch_sales.sale_date, '-', branch_sales.id) as tanggal, 
                branch_sales.savings,
                branch_sales.salesman_note,
                users.name as salesman_name,
                users.position_int as salesman_int_position,
                manager.name as manager_name,
                branches.name as branch_name,
                transferan.transfer_id,
                sum(branch_sales_products.total_price) as stotal_price,
                sum(branch_sales_products.profit_crew) as stotal_crew,
                sum(branch_sales_products.foundation) as stotal_foundation
            ")
            ->whereBetween('branch_sales.sale_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereNull('branch_sales.deleted_at')
            ->where('branch_sales.is_active', '=', 1)
            ->groupBy([
                'branch_sales.id', 'branch_sales.code', 'branch_sales.sale_date',
                'branch_sales.savings', 'branch_sales.salesman_note',
                'users.name', 'users.position_int', 'manager.name', 'branches.name',
                'transferan.transfer_id'
            ]);

        if ($isManager) {
            $baseQuery = $baseQuery->where('branch_sales.manager_id', '=', $user->id);

            $branchId = $request->get('branch_id', -1);
            $salesmanId = $request->get('salesman_id');
            $inBranchIds = ($branchId > 0) ? [$branchId] : $this->neo->myBranches($user)->pluck('id')->toArray();

            $baseQuery = $baseQuery->whereIn('branch_sales.branch_id', $inBranchIds);

            if ($salesmanId > 0) {
                $baseQuery = $baseQuery->where('branch_sales.salesman_id', '=', $salesmanId);
            } else {
                $salesmanIds = $this->getTeams($user, $branchId)->pluck('id')->toArray();
                $baseQuery = $baseQuery->whereIn('branch_sales.salesman_id', $salesmanIds);
            }

            $filters['filter.branchId'] = $branchId;
            $filters['filter.salesmanId'] = $salesmanId;
        } else {
            $baseQuery = $baseQuery->where('branch_sales.salesman_id', '=', $user->id);
        }

        session($filters);

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as jualan"))
            ->mergeBindings($baseQuery);

        $structure = $this->appStructure;
        $canEdit = hasPermission('member.sale.edit');
        $canDelete = hasPermission('member.sale.delete');

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->sale_date);
            })
            ->editColumn('salesman_name', function ($row) use ($structure) {
                $content = "<div>{$row->salesman_name}</div>";
                $posId = intval($row->salesman_int_position);
                $jabatan = $structure->nameById(true, $posId);
                $content .= "<div class=\"text-primary\">{$jabatan}</div>";

                return new HtmlString($content);
            })
            ->addColumn('view', function ($row) use ($canEdit, $canDelete) {
                $buttons = [];

                if (empty($row->transfer_id)) {
                    // if ($canEdit) {
                    //     $routeEdit = route('member.sale.edit', ['branchSaleModifiable' => $row->id]);
                    //     $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" onclick=\"window.location.href='{$routeEdit}';\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt\"></i></button>";
                    // }

                    if ($canDelete) {
                        $routeDelete = route('member.sale.delete', ['branchSaleModifiable' => $row->id]);
                        $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-danger me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDelete}\" title=\"Hapus Penjualan\"><i class=\"fa-solid fa-times mx-1\"></i></button>";
                    }
                }

                $routeView = route('member.sale.detail', ['branchSale' => $row->id]);
                $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-info\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeView}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString(implode('', $buttons));
            })->escapeColumns();

        return $result->toJson();
    }

    private function getTeams(User $user, int $branchId, int $currentId = null, string $search = null): Collection
    {
        $team = $user->active_team_non_manager_list;

        if ($branchId > 0) {
            $team = $team->filter(function ($tm) use ($branchId) {
                return in_array($branchId, $tm->activeBranches->pluck('branch_id')->toArray());
            })->values();
        }

        $current = null;
        if ($currentId > 0) {
            $current = $team->where('id', '=', $currentId)->first();
        }

        if (!empty($current)) {
            $team = $team->reject(function ($tm) use ($currentId) {
                return ($tm->id == $currentId);
            })->values();

            if ($branchId > 0) {
                if (!in_array($branchId, $current->activeBranches->pluck('branch_id')->toArray())) {
                    $current = null;
                }
            }
        }

        if (!is_null($search)) {
            $team = $team->filter(function ($tm) use ($search) {
                $byName = stristr($tm->name, $search);
                $byUsername = stristr($tm->username, $search);
                return (($byName !== false) || ($byUsername !== false));
            })->values();
        }

        $team = $team->sortBy([
            ['position_int', 'asc'],
            ['name', 'asc'],
        ]);

        if (!empty($current)) $team = $team->prepend($current);

        return $team;
    }

    public function salesCrew(Request $request)
    {
        $branchId = intval($request->get('branch'));
        $salesmanId = intval($request->get('current'));
        $search = $request->get('search');
        $user = $request->user();

        $salesman = $this->getTeams($user, $branchId, $salesmanId, $search);

        $result = [];
        foreach ($salesman as $row) {
            $content = '<div class="d-block small"><span class="fw-bold text-decoration-underline">%s</span><span class="ms-2">(%s)</span></div>';
            $result[] = [
                'id' => $row->id,
                'text' => $row->name . ' (' . $row->internal_position_code . ')',
                'html' => sprintf($content, $row->name, $row->internal_position_code),
            ];
        }

        return response()->json($result);
    }

    private function getAvailableProducts($branchId, Carbon|string $dateSale = null, $mode = 'new'): Collection
    {
        $itemsProduct = collect();

        if (empty($branchId) || !in_array($mode, ['new', 'edit'])) return $itemsProduct;
        if (is_null($dateSale)) $dateSale = date('Y-m-d');

        $saleDate = ($dateSale instanceof Carbon) ? $dateSale : Carbon::createFromTimestamp(strtotime($dateSale));

        if ($branchId instanceof Branch) {
            $branch = $branchId;
        } else {
            $branch = Branch::byId($branchId)->byActive()->first();
        }

        if (empty($branch)) return $itemsProduct;

        $branchProducts = $branch->stockProducts($saleDate, true);
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

    private function setItemsProduct($branchId, Carbon|string $saleDate, $mode): Collection
    {
        $itemsProduct = collect();
        if (($stocks = $this->getAvailableProducts($branchId, $saleDate, $mode))->isNotEmpty()) {
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
        $saleDate = resolveTranslatedDate($request->get('date'));

        $itemsProduct = $this->setItemsProduct($branchId, $saleDate, $mode);
        if ($itemsProduct->isNotEmpty()) {
            $responText = view('member.sales.sale-items', [
                'itemsProduct' => $itemsProduct,
                'saleItem' => null,
            ])->render();
        }

        return response($responText, $responCode);
    }

    public function create(Request $request)
    {
        if (!canSale()) {
            return redirect()->route('dashboard')
                ->with('message', 'Tidak dapat melakukan penjualan.')
                ->with('messageClass', 'danger');
        }

        $user = $request->user();
        $branches = $this->neo->myBranches($user);
        $dateRange = $this->neo->dateRangeInputSale();

        return view('member.sales.form', [
            'branches' => $branches,
            'dateRange' => $dateRange,
            'foundationPersen' => SALE_FOUNDATION_PERSEN,
            'windowTitle' => 'Tambah Pembelian',
            'breadcrumbs' => ['Pembelian', 'Tambah']
        ]);
    }

    public function store(Request $request)
    {
        $values = [
            'sale_date' => $request->get('sale_date'),
            'branch_id' => $branchId = $request->get('branch_id'),
            'salesman_id' => $request->get('salesman_id'),
            'product_ids' => $request->get('product_ids', []),
            'product_qty' => $request->get('product_qty', []),
            'product_units' => $request->get('product_units', []),
            'savings' => 0, //$request->get('savings', 0),
            'salesman_note' => $request->get('salesman_note'),
        ];

        $user = $request->user();
        $branches = $this->neo->myBranches($user);
        $branchIds = $branches->pluck('id')->toArray() ?? [mt_rand(10000000001, 99999999999)];

        if (!in_array($branchId, $branchIds)) {
            $responText = view('partials.alert', [
                'message' => "Kantor cabang tidak ditemukan.",
                'messageClass' => 'danger'
            ])->render();

            return response($responText, 404);
        }

        $selectedBranch = $branches->where('id', '=', $branchId)->first();
        $dateSale = date('Y-m-d');

        if (!empty($values['sale_date'])) {
            $values['sale_date'] = $dateSale = resolveTranslatedDate($values['sale_date'], ' ');
        }

        $stocks = $this->getAvailableProducts($selectedBranch, $values['sale_date'], 'new')
            ->whereIn('product_id', $values['product_ids']);

        $inBranchIds = implode(',', $branchIds);
        $inProductIds = implode(',', $stocks->pluck('product_id')->toArray() ?: [mt_rand(10000000001, 99999999999)]);

        $countProducts = count($values['product_ids']);
        $productIdsAlias = [];
        $productQtyAlias = [];
        $maxQtyRules = [];

        $values['product_quantities'] = [];
        $itemValues = [];

        $isAppV2 = isAppV2($dateSale);
        $branchZoneId = $isAppV2 ? $selectedBranch->zone_id : $selectedBranch->wilayah;

        for ($i = 0; $i < $countProducts; $i++) {
            $available = $stocks->where('product_id', '=', $values['product_ids'][$i])->first();
            $product = $available->product;
            $stock = optional($available->stock);

            $productUnit = $product->satuan;
            $saleUnit = ($productUnit == PRODUCT_UNIT_BOX) ? $values['product_units'][$i] : PRODUCT_UNIT_PCS;
            $isNgecer = (($productUnit == PRODUCT_UNIT_BOX) && ($saleUnit == PRODUCT_UNIT_PCS));

            $fnPrice = $isAppV2 ? 'zonePriceV2' : 'zonePrice';
            $fnEceran = $isAppV2 ? 'eceranZonePriceV2' : 'eceranZonePrice';

            $zonePrice = $isNgecer
                ? $product->$fnEceran($branchZoneId)
                : $product->$fnPrice($branchZoneId);

            $qty = $values['product_qty'][$i];
            $totalPrice = $zonePrice * $qty;
            $persenCrew = $product->komisi;
            $profitCrew = floor($persenCrew * $totalPrice / 100);
            $foundationPersen = SALE_FOUNDATION_PERSEN;
            $foundation = floor($foundationPersen * $profitCrew / 100);
            $totalProfit = $totalPrice - $profitCrew - $foundation;

            $itemValues[] = [
                'product_id' => $product->id,
                'branch_product_id' => $stock->branch_product_id,
                'branch_stock_id' => $stock->id,
                'product_unit' => $saleUnit,
                'product_zone' => $branchZoneId,
                'product_price' => $zonePrice,
                'product_qty' => $qty,
                'total_price' => $totalPrice,
                'persen_crew' => $persenCrew,
                'profit_crew' => $profitCrew,
                'persen_foundation' => $foundationPersen,
                'foundation' => $foundation,
                'total_profit' => $totalProfit,
                'is_v2' => $isAppV2,
            ];

            // for validation

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
                    $balance = ($saleUnit == PRODUCT_UNIT_PCS) ? $realStock->pcsBalance : $realStock->boxBalance;
                } else {
                    $balance = $realStock->pcsBalance;
                }
            }

            $maxQtyRules["product_quantities.{$i}.value"] = ['required', 'integer', 'min:1', "max:{$balance}"];
            $values['product_quantities'][$i] = [
                'value' => $qty,
            ];
        }

        $salesmanIds = $this->getTeams($user, intval($branchId))->pluck('id')->toArray();
        if (empty($salesmanIds)) $salesmanIds = [-99999999];
        $inSalesman = implode(',', $salesmanIds);

        $selectedSalesman = User::byId($values['salesman_id'])->first();
        $manager = $selectedSalesman ? $this->neo->managerFromMember($selectedSalesman) : null;
        $managerPosition = null;
        $managerType = null;
        if (!empty($manager) && !empty($selectedBranch)) {
            $managerBranch = $this->neo->branchManagerFromMember($manager, $branchId);
            if (!empty($managerBranch)) {
                $managerPosition = $managerBranch->position_ext;
                $managerType = $managerBranch->manager_type;
            } else {
                $manager = null;
            }
        }

        $values['manager_id'] = $manager ? $manager->id : null;
        $values['manager_position'] = $managerPosition;
        $values['manager_type'] = $managerType;
        $values['salesman_position'] = $selectedSalesman ? $selectedSalesman->position_int : null;

        $validator = Validator::make($values, array_merge([
            'sale_date' => ['required', 'date_format:j F Y'],
            'branch_id' => ['required', "in:{$inBranchIds}"],
            'salesman_id' => [
                'required',
                "in:{$inSalesman}",
            ],
            // 'manager_id' => ['required', 'integer'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', "in:{$inProductIds}", 'distinct'],
            'product_quantities' => ['required', 'array', "size:{$countProducts}"],
            'savings' => ['required', 'integer', 'min:0'],
            'salesman_note' => ['nullable', 'string', 'max:250'],
        ], $maxQtyRules), [
            'salesman_id.in' => 'Salesman tidak terdaftar.',
            'product_ids.*.distinct' => ':attribute tidak boleh ada yang sama.',
            'product_quantities.*.value.max' => 'Persediaan :attribute tidak mencukupi.',
        ], array_merge([
            'sale_date' => 'Tanggal',
            'branch_id' => 'Cabang',
            'salesman_id' => 'Salesman',
            'product_ids' => 'Produk',
            'product_quantities' => 'Jumlah Produk'
        ], $productIdsAlias, $productQtyAlias));

        $responCode = 200;
        $responText = route('member.sale.create');

        if ($validator->fails()) {
            $responCode = 400;
            $responText = $this->validationMessages($validator);
        } else {
            $values['sale_date'] = strtotime($values['sale_date']);
            $values['code'] = BranchSale::makeCode($values['sale_date']);

            if (empty($manager)) {
                $responCode = 400;
                $responText = view('partials.alert', [
                    'message' => 'Manager dari sales tersebut tidak ditemukan.',
                    'messageClass' => 'danger'
                ])->render();
            } else {
                unset($values['product_ids']);
                unset($values['product_qty']);
                unset($values['product_quantities']);

                DB::beginTransaction();
                try {
                    $sale = BranchSale::create($values);
                    foreach ($itemValues as $item) {
                        $item['branch_sale_id'] = $sale->id;
                        BranchSalesProduct::create($item);
                    }

                    DB::commit();

                    session([
                        'message' => "Penjualan berhasil ditambahkan.",
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
        $branchSale = $request->branchSaleModifiable;
        $carbonSaleDate = Carbon::createFromTimestamp(strtotime($branchSale->getRawOriginal('sale_date')));
        $itemsProduct = $this->setItemsProduct($branchSale->branch_id, $carbonSaleDate, 'edit');

        $dateRange = [
            (clone $carbonSaleDate)->startOfWeek(),
            $carbonSaleDate->endOfWeek()
        ];

        if ($dateRange[1]->format('Y-m-d') > date('Y-m-d')) {
            $dateRange[1] = Carbon::today();
        }

        $manager = User::byId($branchSale->manager_id)->first();
        $branches = $this->neo->myBranches($manager);

        return view('member.sales.form-edit', [
            'data' => $branchSale,
            'itemsProduct' => $itemsProduct,
            'branches' => $branches,
            'dateRange' => $dateRange,
            'foundationPersen' => SALE_FOUNDATION_PERSEN,
            'windowTitle' => 'Edit Penjualan',
            'breadcrumbs' => ['Penjualan', 'Edit']
        ]);
    }

    public function update(Request $request)
    {
        $branchSale = $request->branchSaleModifiable;
        $branchSaleItems = $branchSale->products;

        $values = [
            'sale_date' => $request->get('sale_date'),
            'branch_id' => $branchId = $request->get('branch_id'),
            'salesman_id' => $request->get('salesman_id'),
            'product_ids' => $request->get('product_ids', []),
            'product_qty' => $request->get('product_qty', []),
            'savings' => 0, //$request->get('savings', 0),
            'salesman_note' => $request->get('salesman_note'),
        ];

        $user = $request->user();
        $branches = $this->neo->myBranches($user);
        $branchIds = $branches->pluck('id')->toArray() ?? [mt_rand(10000000001, 99999999999)];
        $selectedBranch = $branches->where('id', '=', $branchId)->first();

        if (!empty($values['sale_date'])) {
            $values['sale_date'] = resolveTranslatedDate($values['sale_date'], ' ');
        }

        $stocks = $this->getAvailableProducts($selectedBranch, $values['sale_date'], 'edit')
            ->where('stock.stock', '>', 0);

        $products = $stocks->pluck('product');
        $inBranchIds = implode(',', $branchIds);
        $inProductIds = implode(',', $products->pluck('id')->toArray() ?: [mt_rand(10000000001, 99999999999)]);

        $countProducts = count($values['product_ids']);
        $productIdsAlias = [];
        $productQtyAlias = [];
        $maxQtyRules = [];

        $values['product_quantities'] = [];

        for ($i = 0; $i < $countProducts; $i++) {
            $prodId = "product_ids.{$i}";
            $prodQty = "product_quantities.{$i}.value";
            $x = $i + 1;
            $title = "Product yang ke-{$x}";
            $productIdsAlias[$prodId] = $title;
            $productQtyAlias[$prodQty] = "Jumlah {$title}";

            $checkProductId = $values['product_ids'][$i];

            $inputProduct = $stocks->where('product_id', '=', $checkProductId)->first();
            $balance = $inputProduct ? $inputProduct->stock->balance : 0;

            $oldItem = $branchSaleItems->where('product_id', '=', $checkProductId)->first();

            if (!empty($oldItem)) {
                $balance += $oldItem->product_qty;
            }

            $maxQtyRules["product_quantities.{$i}.value"] = ['required', 'integer', 'min:1', "max:{$balance}"];
            $values['product_quantities'][$i] = [
                'value' => $values['product_qty'][$i],
            ];
        }

        $salesmanIds = $this->getTeams($user, intval($branchId))->pluck('id')->toArray();
        if (empty($salesmanIds)) $salesmanIds = [-99999999];
        $inSalesman = implode(',', $salesmanIds);

        $selectedSalesman = User::byId($values['salesman_id'])->first();
        $manager = $selectedSalesman ? $this->neo->managerFromMember($selectedSalesman) : null;
        $managerPosition = null;
        $managerType = null;
        if (!empty($manager) && !empty($selectedBranch)) {
            $managerBranch = $this->neo->branchManagerFromMember($manager, $branchId);
            if (!empty($managerBranch)) {
                $managerPosition = $managerBranch->position_ext;
                $managerType = $managerBranch->manager_type;
            } else {
                $manager = null;
            }
        }

        $values['manager_id'] = $manager ? $manager->id : null;
        $values['manager_position'] = $managerPosition;
        $values['manager_type'] = $managerType;
        $values['salesman_position'] = $selectedSalesman ? $selectedSalesman->position_int : null;

        $validator = Validator::make($values, array_merge([
            'sale_date' => ['required', 'date_format:j F Y'],
            'branch_id' => ['required', "in:{$inBranchIds}"],
            'salesman_id' => [
                'required',
                "in:{$inSalesman}",
            ],
            // 'manager_id' => ['required', 'integer'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['required', "in:{$inProductIds}", 'distinct'],
            'product_quantities' => ['required', 'array', "size:{$countProducts}"],
            'savings' => ['required', 'integer', 'min:0'],
            'salesman_note' => ['nullable', 'string', 'max:250'],
        ], $maxQtyRules), [
            'salesman_id.in' => 'Salesman tidak terdaftar.',
            'product_quantities.*.value.max' => 'Persediaan :attribute tidak mencukupi.',
        ], array_merge([
            'sale_date' => 'Tanggal',
            'branch_id' => 'Cabang',
            'salesman_id' => 'Salesman',
            'product_ids' => 'Produk',
            'product_quantities' => 'Jumlah Produk'
        ], $productIdsAlias, $productQtyAlias));

        $responCode = 200;
        $responText = route('member.sale.index');

        if ($validator->fails()) {
            $responCode = 400;
            $responText = $this->validationMessages($validator);
        } else {
            $newValues = array_merge($branchSale->toArray(), $values);
            unset($newValues['id'], $newValues['created_at'], $newValues['updated_at'], $newValues['deleted_at']);

            $itemValues = [];
            for ($i = 0; $i < $countProducts; $i++) {
                $stock = $stocks->where('product_id', '=', $values['product_ids'][$i])->first();

                $zonePrice = $stock->product->zonePrice($selectedBranch->wilayah);
                $qty = $values['product_qty'][$i];
                $totalPrice = $zonePrice * $qty;
                $persenCrew = $stock->product->komisi;
                $profitCrew = floor($persenCrew * $totalPrice / 100);
                $foundationPersen = SALE_FOUNDATION_PERSEN;
                $foundation = floor($foundationPersen * $profitCrew / 100);
                $totalProfit = $totalPrice - $profitCrew - $foundation;

                $itemValues[] = [
                    'product_id' => $stock->product_id,
                    'branch_product_id' => $stock->stock->branch_product_id,
                    'branch_stock_id' => $stock->stock->id,
                    'product_unit' => $stock->product->satuan,
                    'product_zone' => $selectedBranch->wilayah,
                    'product_price' => $zonePrice,
                    'product_qty' => $qty,
                    'total_price' => $totalPrice,
                    'persen_crew' => $persenCrew,
                    'profit_crew' => $profitCrew,
                    'persen_foundation' => $foundationPersen,
                    'foundation' => $foundation,
                    'total_profit' => $totalProfit,
                ];
            }

            BranchSale::applyDeletedCode($branchSale);

            DB::beginTransaction();
            try {
                foreach ($branchSaleItems as $oldItem) {
                    $originalOldItemTimestamp = $oldItem->timestamps;
                    $oldItem->timestamps = false;
                    $oldItem->is_active = false;
                    $oldItem->save();
                    $oldItem->timestamps = $originalOldItemTimestamp;

                    $oldItem->delete();
                }

                $originalSaleTimestamp = $branchSale->timestamps;
                $branchSale->timestamps = false;
                $branchSale->is_active = false;
                $branchSale->deleted_at = date('Y-m-d H:i:s');
                $branchSale->save();
                $branchSale->timestamps = $originalSaleTimestamp;

                $sale = BranchSale::create($newValues);
                foreach ($itemValues as $item) {
                    $item['branch_sale_id'] = $sale->id;
                    BranchSalesProduct::create($item);
                }

                DB::commit();

                session([
                    'message' => "Penjualan berhasil diganti.",
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi." . (isLive() ? '' : ' ' . $e->getMessage()),
                    'messageClass' => 'danger'
                ])->render();
            }
        }

        return response($responText, $responCode);
    }

    public function delete(Request $request)
    {
        return $this->detail($request);
    }

    public function destroy(Request $request)
    {
        $branchSale = $request->branchSaleModifiable;

        $code = $branchSale->code;
        $responCode = 200;
        $responText = route('member.sale.index');

        DB::beginTransaction();
        try {
            $branchSale->delete();

            DB::commit();

            session([
                'message' => "Penjualan dengan kode <b>{$code}</b> berhasil dihapus.",
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => "Telah terjadi kesalahan pada server. Silahkan coba lagi." . (isLive() ? '' : ' ' . $e->getMessage()),
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }

    public function detail(Request $request)
    {
        $isDelete = ($request->route()->getName() === 'member.sale.delete');
        $branchSale = $isDelete ? $request->branchSaleModifiable : $request->branchSale;

        $productDetails = collect();
        foreach ($branchSale->products as $product) {
            $detail = (object) [
                'category_id' => $product->product->product_category_id,
                'category_name' => $product->product->category->name,
                'product_id' => $product->product_id,
                'product_name' => $product->product->name,
                'product_unit' => $product->product->product_unit,
                'product_price' => $product->product_price,
                'product_qty' => $product->product_qty,
                'product_unit_sale' => $product->product_unit_name,
                'total_price' => $product->total_price,
            ];

            $productDetails->push($detail);
        }

        return view('member.sales.sale-detail', [
            'deleteData' => $isDelete,
            'branchSale' => $branchSale,
            'productDetails' => $productDetails->sortBy('category_name')
        ]);
    }
}
