<?php

namespace App\Http\Controllers\Main;

use App\Helpers\AppStructure;
use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchSale;
use App\Models\BranchSalesProduct;
use App\Models\User;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Spatie\SimpleExcel\SimpleExcelWriter;

class SalesController extends Controller
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
        $branches = Branch::orderBy('name')->byActive()->get();
        $dateRange = $this->dateFilter();
        $currentBranchId = session('filter.branchId', -1);
        $currentSalesmanId = session('filter.salesmanId', -1);
        $currentSalesman = ($currentSalesmanId > 0) ? User::byId($currentSalesmanId)->first() : null;

        return view('main.sales.index', [
            'dateRange' => $dateRange,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'currentSalesman' => $currentSalesman,
            'windowTitle' => 'Daftar Penjualan',
            'breadcrumbs' => ['Penjualan', 'Daftar']
        ]);
    }

    public function datatable(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));
        $salesmanId = intval($request->get('salesman_id'));
        $canEdit = hasPermission('main.sales.edit');
        $canDelete = hasPermission('main.sales.delete');

        $branches = Branch::byActive();
        if ($branchId != -1) $branches = $branches->byId($branchId);
        $branches = $branches->get();
        $inBranchIds = $branches->pluck('id')->toArray();

        $strWhereTransfer = implode(',', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
        $transferQuery = DB::table('branch_transfers')
            ->join('branch_transfer_details', 'branch_transfer_details.transfer_id', '=', 'branch_transfers.id')
            ->selectRaw('
                branch_transfer_details.sale_id,
                branch_transfer_details.transfer_id,
                COUNT(branch_transfer_details.id) as temp_count
            ')
            ->whereRaw("(branch_transfers.deleted_at is null) AND (branch_transfers.transfer_status in($strWhereTransfer))")
            ->groupByRaw('branch_transfer_details.sale_id, branch_transfer_details.transfer_id')
            ->toSql();

        $baseQuery = DB::table('branch_sales')
            ->join(DB::raw('users as salesman'), 'salesman.id', '=', 'branch_sales.salesman_id')
            ->join(DB::raw('users as manager'), 'manager.id', '=', 'branch_sales.manager_id')
            ->join('branch_sales_products', 'branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
            ->join('products', 'products.id', '=', 'branch_sales_products.product_id')
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
                branch_sales.salesman_id,
                salesman.name as salesman_name,
                salesman.position_int as salesman_int_position,
                salesman.position_ext as salesman_ext_position,
                branch_sales.manager_id,
                manager.name as manager_name,
                branch_sales.manager_position,
                branch_sales.manager_type,
                branches.name as branch_name,
                transferan.transfer_id,
                sum(branch_sales_products.total_price) as stotal_sale,
                null as purchase_items,
                GROUP_CONCAT(products.name) as product_names,
                GROUP_CONCAT(branch_sales_products.product_qty) as product_quantities,
                GROUP_CONCAT(branch_sales_products.product_price) as product_prices,
                GROUP_CONCAT(branch_sales_products.product_unit) as product_units,
                GROUP_CONCAT(branch_sales_products.total_price) as total_product_prices
            ")
            ->whereBetween('branch_sales.sale_date', [$formatStart, $formatEnd])
            ->whereIn('branch_sales.branch_id', $inBranchIds)
            ->whereNull('branch_sales.deleted_at')
            ->whereNull('branch_sales_products.deleted_at')
            ->where('branch_sales.is_active', '=', true)
            ->where('branch_sales_products.is_active', '=', true)
            ->groupBy([
                'branch_sales.id', 'branch_sales.code', 'branch_sales.sale_date',
                'branch_sales.savings', 'branch_sales.salesman_note',
                'branch_sales.salesman_id', 'salesman.name', 'salesman.position_int', 'salesman.position_ext',
                'branch_sales.manager_id', 'manager.name',
                'branch_sales.manager_position', 'branch_sales.manager_type',
                'branches.name', 'transferan.transfer_id'
            ]);

        if ($salesmanId > 0) {
            $baseQuery = $baseQuery->where('branch_sales.salesman_id', '=', $salesmanId);
        }

        session([
            'filter.dates' => ['start' => $startDate, 'end' => $endDate],
            'filter.branchId' => $branchId,
            'filter.salesmanId' => $salesmanId,
        ]);

        $structure = $this->appStructure;

        $query = DB::table(DB::raw("({$baseQuery->toSql()}) as jualan"))
            ->mergeBindings($baseQuery);

        $result = datatables()->query($query)
            ->editColumn('tanggal', function ($row) {
                return formatFullDate($row->sale_date);
            })
            ->editColumn('purchase_items', function ($row) {
                $itemsName = explode(',', $row->product_names);
                $itemsQty = explode(',', $row->product_quantities);
                $itemsUnit = explode(',', $row->product_units);

                $collect = collect();
                foreach ($itemsName as $index => $name) {
                    $collect->push((object) [
                        'name' => $name,
                        'qty' => formatNumber($itemsQty[$index]),
                        'unit' => strtoupper(Arr::get(PRODUCT_UNITS, $itemsUnit[$index], '')),
                    ]);
                }

                $collect = $collect->sortBy(['name', 'asc']);

                $html = '<ul class="mb-0 ps-3">';
                foreach ($collect as $col) {
                    $name = $col->name;
                    $qty = $col->qty;
                    $unit = $col->unit;
                    $html .= "<li><div class=\"d-flex flex-nowrap justify-content-between\"><span class=\"me-2\">{$name}</span><span class=\"ms-2\">{$qty} {$unit}</span></div></li>";
                }
                $html .= '</ul>';

                return new HtmlString($html);
            })
            ->editColumn('salesman_name', function ($row) use ($structure) {
                $content = "<div><span>{$row->salesman_name}</span></div>";
                if (($row->salesman_int_position >= USER_INT_MGR)) {
                    $jabatan = $structure->codeById(true, intval($row->salesman_int_position));
                    $content .= "<div class=\"ms-2 text-primary\">{$jabatan}</div>";
                }

                return new HtmlString($content);
            })
            ->editColumn('manager_name', function ($row) use ($structure) {
                $content = "<div>{$row->manager_name}</div>";
                $infos = [];

                $posEx = intval($row->manager_position);
                if (in_array($posEx, [USER_EXT_DIST, USER_EXT_AG])) {
                    $jabatan = $structure->codeById(false, $posEx);
                    if (!empty($jabatan)) $infos[] = $jabatan;

                    if ($posEx == USER_EXT_DIST) {
                        $type = Arr::get(USER_BRANCH_MANAGER_CODES, $row->manager_type ?? 'x');
                        if (!empty($type)) $infos[] = $type;
                    }

                    if (!empty($infos)) {
                        $info = implode(' - ', $infos);
                        $content .= "<div class=\"ms-2 text-primary\">{$info}</div>";
                    }
                }

                return new HtmlString($content);
            })
            ->addColumn('view', function ($row) use ($canEdit, $canDelete) {
                $buttons = [];

                if (empty($row->transfer_id)) {
                    // if ($canEdit) {
                    //     $routeEdit = route('main.sales.edit', ['branchSaleModifiable' => $row->id]);
                    //     $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" onclick=\"window.location.href='{$routeEdit}';\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt\"></i></button>";
                    // }

                    if ($canDelete) {
                        $routeDelete = route('main.sales.delete', ['branchSaleModifiable' => $row->id]);
                        $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-danger me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDelete}\" title=\"Hapus Penjualan\"><i class=\"fa-solid fa-times mx-1\"></i></button>";
                    }
                }

                $routeDetail = route('main.sales.detail', ['branchSale' => $row->id]);
                $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-info\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDetail}\" title=\"Detail\"><i class=\"fa-solid fa-eye\"></i></button>";

                return new HtmlString(implode('', $buttons));
            })->escapeColumns();

        return $result->toJson();
    }

    private function getTeams(User $user, int $branchId, int $currentId = null, string $search = null): Collection
    {
        $team = $user->active_team_non_manager_list;
        $team = $team->prepend($user);

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

    private function getAvailableProducts($branchId, $dateSale = null, $mode = 'new'): Collection
    {
        $itemsProduct = collect();

        if (empty($branchId) || !in_array($mode, ['new', 'edit'])) return $itemsProduct;
        if (is_null($dateSale)) $dateSale = date('Y-m-d');

        $today = Carbon::today()->startOfWeek(DAY_STOCKOPNAME_START);
        $saleDate = ($dateSale instanceof Carbon) ? $dateSale : Carbon::createFromTimestamp(strtotime($dateSale));
        $dates = $this->neo->dateRangeStockOpname($saleDate);
        $isCurrent = ($today->diffInDays($dates->start) == 0);

        if ($branchId instanceof Branch) {
            $branch = $branchId;
        } else {
            $branch = Branch::byId($branchId)
                ->with(['currentWeekSales', 'previousWeekSales', 'beforePreviousWeekSales', 'productsFull', 'products'])
                ->first();
        }

        if (empty($branch)) return $itemsProduct;

        $activeProductIds = $branch->products->pluck('product_id')->toArray();
        $stocks = $branch->stock_products->summariesStock->whereIn('product_id', $activeProductIds);
        $result = collect();
        foreach ($stocks as $stock) {
            $summary = $isCurrent ? $stock->summary->current : $stock->summary->previous;

            if ($summary->stock <= 0) continue;

            if ($mode == 'new') {
                if ($summary->balance <= 0) continue;
            }

            $obj = clone $stock;
            unset($obj->summary);
            $obj->stock = $summary;
            $result->push($obj);
        }

        return $result->sortBy([['category_name', 'asc'], ['product_name', 'asc']]);
    }

    private function setItemsProduct($branchId, $saleDate, $mode): Collection
    {
        $itemsProduct = collect();
        if (($stocks = $this->getAvailableProducts($branchId, $saleDate, $mode))->isNotEmpty()) {
            foreach ($stocks as $stock) {
                $category = $itemsProduct->where('category_id', '=', $stock->category_id)->first();
                $product = $stock->product;
                if (empty($category)) {
                    $obj = (object) [
                        'category_id' => $stock->category_id,
                        'category' => $stock->product->category,
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
            $responText = view('main.sales.sale-items', [
                'itemsProduct' => $itemsProduct,
                'saleItem' => null,
            ])->render();
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

        return view('main.sales.form-edit', [
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

        $manager = $branchSale->manager;
        $branches = $this->neo->myBranches($manager);
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

        $salesmanIds = $this->getTeams($manager, intval($branchId))->pluck('id')->toArray();
        if (empty($salesmanIds)) $salesmanIds = [-99999999];
        $inSalesman = implode(',', $salesmanIds);

        $selectedSalesman = User::byId($values['salesman_id'])->first();
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
            'manager_id' => ['required', 'integer'],
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
        $responText = route('main.sales.index');

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
        $responText = route('main.sales.index');

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
        $isDelete = ($request->route()->getName() === 'main.sales.delete');
        $branchSale = $isDelete ? $request->branchSaleModifiable : $request->branchSale;

        $productDetails = collect();
        foreach ($branchSale->products as $product) {
            $detail = (object) [
                'category_id' => $product->product->product_category_id,
                'category_name' => $product->product->category->name,
                'product_id' => $product->product_id,
                'product_name' => $product->product->name,
                'product_price' => $product->product_price,
                'product_qty' => $product->product_qty,
                'product_unit_name' => $product->product_unit_name,
                'total_price' => $product->total_price,
            ];

            $productDetails->push($detail);
        }

        return view('main.sales.sale-detail', [
            'deleteData' => $isDelete,
            'branchSale' => $branchSale,
            'productDetails' => $productDetails->sortBy('category_name')
        ]);
    }

    // download
    private function downloadQuery(Request $request)
    {
        $start_date = resolveTranslatedDate($request->get('start_date') ?: date('j F Y'), ' ');
        $end_date = resolveTranslatedDate($request->get('end_date') ?: date('j F Y'), ' ');
        $startDate = Carbon::createFromTimestamp(strtotime($start_date));
        $endDate = Carbon::createFromTimestamp(strtotime($end_date));
        $formatStart = $startDate->format('Y-m-d');
        $formatEnd = $endDate->format('Y-m-d');

        list($formatStart, $formatEnd, $startDate, $endDate) = var1LowestEqualVar2($formatStart, $formatEnd, [$formatStart, $formatEnd, $startDate, $endDate]);

        $branchId = intval($request->get('branch_id', -1));
        $salesmanId = intval($request->get('salesman_id', 0));

        $branches = Branch::byActive();
        if ($branchId > 0) $branches = $branches->byId($branchId);
        $branches = $branches->get();
        $inBranchIds = $branches->pluck('id')->toArray();

        $query = DB::table('branch_sales')
            ->join('users', 'users.id', '=', 'branch_sales.salesman_id')
            ->join(DB::raw('users as manager'), 'manager.id', '=', 'branch_sales.manager_id')
            ->join('branch_sales_products', 'branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
            ->join('branches', 'branches.id', '=', 'branch_sales.branch_id')
            ->join('products', 'products.id', '=', 'branch_sales_products.product_id')
            ->join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->selectRaw("
                branch_sales.id,
                branch_sales.code,
                branch_sales.sale_date,
                branch_sales.savings,
                branch_sales.salesman_note,
                branch_sales.salesman_id,
                users.name as salesman_name,
                users.position_int as salesman_int_position,
                users.position_ext as salesman_ext_position,
                branch_sales.manager_id,
                manager.name as manager_name,
                manager.position_ext as manager_position,
                branches.name as branch_name,
                products.product_category_id,
                products.name as product_name,
                product_categories.name as product_category_name,
                branch_sales_products.product_qty,
                branch_sales_products.product_price,
                branch_sales_products.total_price
            ")
            ->whereBetween('branch_sales.sale_date', [$formatStart, $formatEnd])
            ->whereIn('branch_sales.branch_id', $inBranchIds)
            ->whereNull('branch_sales.deleted_at')
            ->whereNull('branch_sales_products.deleted_at')
            ->where('branch_sales.is_active', '=', true)
            ->where('branch_sales_products.is_active', '=', true);

        if ($salesmanId > 0) {
            $query = $query->where('branch_sales.salesman_id', '=', $salesmanId);
        }

        return (object) [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'branches' => $branches,
            'salesmanId' => $salesmanId,
            'rows' => $query->orderBy('branch_sales.sale_date')
                ->orderBy('branches.name')
                ->orderBy('manager.position_ext')
                ->orderBy('manager.name')
                ->orderBy('users.position_int')
                ->orderBy('users.name')
                ->orderBy('branch_sales.id')
                ->orderBy('product_categories.name')
                ->orderBy('products.name')
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

        $branchId = $data->branchId;
        $startFormatted = $data->startDate->format('Ymd');
        $endFormatted = $data->endDate->format('Ymd');
        $tglName = $startFormatted;
        $tglReport = formatFullDate($data->startDate);

        if ($startFormatted != $endFormatted) {
            if ($startFormatted > $endFormatted) {
                $tglName = "{$endFormatted}-{$startFormatted}";
                $tglReport = formatFullDate($data->endDate) . ' s/d ' . $tglReport;
            } else {
                $tglName = "{$startFormatted}-{$endFormatted}";
                $tglReport = $tglReport . ' s/d ' . formatFullDate($data->endDate);
            }
        }

        $downloadName = "Penjualan-{$tglName}";

        $titleBranch = 'Semua';
        if ($branchId > 0) {
            $branch = $data->branches->first();
            $titleBranch = $branch->name;
            $downloadName .= '-' . strtolower(str_replace(['-', ' ', '.', ','], ['_', '_', '_', '_'], $titleBranch));
        }

        $downloadName = $downloadName . ".xlsx";
        $appStructure = $this->appStructure;

        $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->build();
        $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->build();
        $rowStyle = (new StyleBuilder)->setFontSize(10)->build();
        $excel = SimpleExcelWriter::streamDownload($downloadName)->noHeaderRow()
            ->addRow([
                '',
            ])
            ->addRow([
                'LAPORAN PENJUALAN',
            ], $titleStyle)
            ->addRow([
                '',
            ])
            ->addRow([
                'Tanggal:', $tglReport,
            ], $titleStyle)
            ->addRow([
                'Cabang:', $titleBranch
            ], $titleStyle)
            ->addRow([
                '',
            ])
            ->addRow([
                'Tanggal', 'Kode', 'Salesman', 'Posisi', 'Manager', 'Cabang', 'Produk', 'QTY', 'Harga', 'Total', 'Catatan'
            ], $headerStyle);

        $saleId = 0;
        foreach ($rows as $row) {
            $isSameSale = ($row->id == $saleId);

            $cells = [
                $isSameSale ? '' : $row->sale_date,
                $isSameSale ? '' : $row->code,
                $isSameSale ? '' : $row->salesman_name,
                $isSameSale ? '' : $appStructure->nameById(true, $row->salesman_int_position),
                $isSameSale ? '' : (($row->salesman_id == $row->manager_id) ? '-' : $row->manager_name),
                $isSameSale ? '' : $row->branch_name,
                $row->product_name,
                intval($row->product_qty),
                intval($row->product_price),
                intval($row->total_price),
                $row->salesman_note ?? '',
            ];
            $excel = $excel->addRow($cells, $rowStyle);

            $saleId = $row->id;
        }

        $excel->toBrowser();
    }
}
