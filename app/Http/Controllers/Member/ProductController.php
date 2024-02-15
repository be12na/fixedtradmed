<?php

namespace App\Http\Controllers\Member;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\BranchProduct;
use App\Models\BranchSale;
use App\Models\BranchStock;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Exists;

class ProductController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $categories = ProductCategory::orderBy('name')->orderBy('merek')->with(['products' => function ($query) {
            return $query->orderBy('name');
        }])->get();

        return view('member.products.product-index', [
            'categories' => $categories,
            'windowTitle' => 'Daftar Produk',
            'breadcrumbs' => ['Produk', 'Daftar']
        ]);
    }

    public function indexStock(Request $request)
    {
        if ($request->route()->getName() == 'member.product.stock.productStock') {
            $branch = $request->branch;
            $branchProducts = $branch->products;
            $inProducts = $branchProducts->pluck('product_id')->toArray();

            $categories = ProductCategory::byActive()->orderBy('name')->orderBy('merek')
                ->whereHas('products', function ($pr) use ($inProducts) {
                    return $pr->byActive()->whereIn('id', $inProducts);
                })
                ->with(['products' => function ($query) use ($inProducts) {
                    return $query->byActive()->orderBy('name')->whereIn('id', $inProducts);
                }])
                ->get();

            $prods = collect();
            foreach ($categories as $category) {
                foreach ($category->products as $product) {
                    $branchProduct = $branchProducts->where('product_id', '=', $product->id)->first();
                    $branchStock = $branchProduct ? $branchProduct->currentStock : null;
                    $branchStock = optional($branchStock);
                    $canInput = (($branchStock->stock_type ?? 0) == STOCK_FLAG_MANAGER);

                    $prod = (object) [
                        'category' => $category,
                        'product' => $product,
                        'countProduct' => $canInput ? ($branchStock->input_manager ?? 0) : ($branchStock->total_stock ?? 0),
                        'canInput' => $canInput,
                    ];

                    $prods->push($prod);
                }
            }

            session(['filter.branchId' => $branch->id]);

            return view('member.products.stock-list', [
                'branch' => $branch,
                'products' => $prods
            ]);
        }

        $user = $request->user();
        $branches = $user->branches_stock;
        $branchIds = $branches->pluck('id')->toArray();
        $currentBranchId = session('filter.branchId', -1);

        if (!in_array($currentBranchId, $branchIds)) $currentBranchId = $branchIds[0];

        return view('member.products.stock-index', [
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'windowTitle' => 'Stock Opname',
            'breadcrumbs' => ['Produk', 'Stock Opname']
        ]);
    }

    public function inputStock(Request $request)
    {
        $currentBranchId = session('filter.branchId', -1);

        $user = $request->user();
        $product = $request->product;
        $branch = $request->branch;

        if ($branch->id != $currentBranchId) {
            return ajaxError('Kantor cabang belum dipilih.', 404);
        }

        $branchIds = $user->branches_stock->pluck('id')->toArray();

        if (!in_array($branch->id, $branchIds)) {
            $branchName = $branch->name;
            return ajaxError("Anda tidak dapat melakukan pengisian persediaan pada cabang {$branchName}", 403);
        }

        $branchProducts = $branch->products;
        $branchProduct = $branchProducts->where('product_id', '=', $product->id)->first();
        $branchStock = optional(optional($branchProduct)->currentStock);

        return view('member.products.stock-form', [
            'branch' => $branch,
            'product' => $product,
            'currentStock' => $branchStock->input_manager ?? 0,
            'modalHeader' => (empty($branchStock->id) ? 'Input' : 'Edit') . ' Stock Opname',
            'postUrl' => route('member.product.stock.save', ['branch' => $branch->id, 'product' => $product->id]),
        ]);
    }

    public function saveStock(Request $request)
    {
        $currentBranchId = session('filter.branchId', -1);

        $user = $request->user();
        $product = $request->product;
        $branch = $request->branch;

        if ($branch->id != $currentBranchId) {
            return response($this->validationMessages('Kantor cabang belum dipilih.'), 404);
        }

        $branchIds = $user->branches_stock->pluck('id')->toArray();

        if (!in_array($branch->id, $branchIds)) {
            $branchName = $branch->name;
            return response($this->validationMessages("Anda tidak dapat melakukan pengisian persediaan pada cabang {$branchName}"), 403);
        }

        $existsProduct = (new Exists('branch_products', 'product_id'))->where('branch_id', $branch->id)->where('is_active', true);

        $validator = Validator::make($values = $request->only(['product_id', 'input_manager']), [
            'product_id' => ['required', 'integer', $existsProduct],
            'input_manager' => 'required|integer|min:0'
        ], [], [
            'product_id' => 'Produk',
            'input_manager' => 'Jumlah persediaan'
        ]);

        if ($validator->fails()) {
            return response($this->validationMessages($validator), 400);
        }

        $branchProducts = $branch->products;
        $branchProduct = $branchProducts->where('product_id', '=', $product->id)->first();
        $newProduct = empty($branchProduct);

        $branchProductValues = [];
        $time = time();
        $newStock = true;
        $branchStock = null;
        $thisWeek = $this->neo->dateRangeStockOpname($time, 'Y-m-d');

        if (!$newProduct) {
            $branchStock = ($newStock = empty($branchProduct->currentStock)) ? $branchProduct->lastStock : $branchProduct->currentStock;
        } else {
            $branchProductValues = [
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'active_at' => $time
            ];
        }

        $lastStockOpmane = optional($branchStock);
        $realStock = optional($lastStockOpmane->real_stock);

        $branchStockValues = [
            'date_from' => $thisWeek->start,
            'date_to' => $thisWeek->end,
            'stock_type' => STOCK_FLAG_MANAGER,
            'stock_info' => Arr::get(STOCK_FLAG_INFOS, STOCK_FLAG_MANAGER),
            'created_by' => $user->id,
        ];

        $isBox = ($product->satuan == PRODUCT_UNIT_BOX);
        $totalStock = $isBox ? ($realStock->boxStock ?? 0) : ($realStock->pcsStock ?? 0);
        $branchStockValues['last_stock'] = $last_stock = $newStock ? $totalStock : ($lastStockOpmane->last_stock ?? 0);

        $output = $isBox ? ($realStock->boxOut ?? 0) : ($realStock->pcsOut ?? 0);

        // $sumStocks = $branch->stock_products->summariesStock->where('product_id', '=', $product->id)->first();
        // $output = $sumStocks ? $sumStocks->summary->previous->output : 0;

        $branchStockValues['output_stock'] = $output;
        $branchStockValues['rest_stock'] = $rest = $last_stock - $output;
        $branchStockValues['input_manager'] = $inputValue = intval($values['input_manager']);
        $branchStockValues['diff_stock'] = $diff = (($last_stock > 0) ? $inputValue - $rest : 0);
        $branchStockValues['input_admin'] = 0;
        $branchStockValues['total_stock'] = ($diff == 0) ? $inputValue : 0;

        $responCode = 200;
        $responText = route('member.product.stock.index');

        DB::beginTransaction();
        try {
            if ($newProduct) {
                $branchProduct = BranchProduct::create($branchProductValues);
            }

            if ($newStock) {
                $branchStockValues['branch_product_id'] = $branchProduct->id;
                BranchStock::create($branchStockValues);
            } else {
                $branchStock->update($branchStockValues);
            }

            DB::commit();

            session([
                'message' => "Persediaan produk berhasil disimpan.",
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $responCode = 500;
            $message = 'Telah terjadi kesalahan pada server. Silahkan coba lagi. ' . (!isLive() ? $e->getMessage() : '');
            $responText = $this->validationMessages($message);
        }

        return response($responText, $responCode);
    }
}
