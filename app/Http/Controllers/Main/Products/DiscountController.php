<?php

namespace App\Http\Controllers\Main\Products;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Unique;

class DiscountController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $product = $request->product;
        $currentZoneId = session('filter.zoneId') ?? 1;

        return view('main.products.discount.index', [
            'product' => $product,
            'zones' => $this->neo->zones(true),
            'currentZoneId' => $currentZoneId,
            'windowTitle' => 'Daftar Diskon Produk',
            'breadcrumbs' => ['Master', 'Produk', 'Diskon']
        ]);
    }

    public function datatable(Request $request)
    {
        $product = $request->product;
        $zoneId = $request->get('zone_id');
        $canEdit = hasPermission('main.master.product.discount.edit');
        $canDelete = hasPermission('main.master.product.discount.remove');
        $discounts = $product->mitraDiscount()->byZone($zoneId)->orderBy('min_qty')->get();
        $productId = $product->id;

        session(['filter.zoneId' => $zoneId]);

        $query = DB::table('product_discounts')
            ->selectRaw("
            product_discounts.id, product_discounts.discount_category, 
            product_discounts.min_qty, product_discounts.discount
            ")
            ->where('product_discounts.product_id', '=', $productId)
            ->where('product_discounts.zone_id', '=', $zoneId)
            ->where('product_discounts.mitra_type', '=', MITRA_TYPE_AGENT)
            ->whereNull('product_discounts.deleted_at')
            ->orderByRaw("product_discounts.discount_category, product_discounts.min_qty");

        return datatables()->query($query)
            ->addColumn('discount_category_name', function ($row) {
                return Arr::get(MITRA_DISCOUNT_CATEGORIES, $row->discount_category, '-');
            })
            ->editColumn('min_qty', function ($row) use ($discounts) {
                $result = formatNumber($row->min_qty, 0) . ' s/d ';
                $discount = $discounts->where('min_qty', '>', $row->min_qty)->first();

                if (!empty($discount)) {
                    $result .= formatNumber($discount->min_qty - 1, 0);
                } else {
                    $result .= ' UP';
                }

                return $result;
            })
            ->addColumn('view', function ($row) use ($canEdit, $canDelete, $productId) {
                $buttons = [];

                if ($canEdit) {
                    $routeEdit = route('main.master.product.discount.edit', ['product' => $productId, 'mitraDiscount' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-success me-1\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeEdit}\" title=\"Edit\"><i class=\"fa-solid fa-pencil-alt mx-1\"></i></button>";
                }

                if ($canDelete) {
                    $routeDelete = route('main.master.product.discount.remove', ['product' => $productId, 'mitraDiscount' => $row->id]);
                    $buttons[] = "<button type=\"button\" class=\"btn btn-sm btn-outline-danger\" data-bs-toggle=\"modal\" data-bs-target=\"#my-modal\" data-modal-url=\"{$routeDelete}\" title=\"Hapus\"><i class=\"fa-solid fa-trash mx-1\"></i></button>";
                }

                return new HtmlString(implode('', $buttons));
            })
            ->escapeColumns()
            ->toJson();
    }

    private function validateInput(array $values, ProductPrice $productPrice = null, ProductDiscount $productDiscount = null)
    {
        $result = ['status' => true, 'message' => ''];

        if (empty($productPrice)) {
            $result['status'] = false;
            $result['message'] = $this->validationMessages('Harga produk belum ditentukan.');
        } else {
            $inCategories = implode(',', array_keys(Arr::where(MITRA_DISCOUNT_CATEGORIES, function ($k, $v) {
                return ($k > 0);
            })));

            $inZones = implode(',', $this->neo->zones(true)->pluck('id')->toArray());
            $maxPrice = optional($productPrice)->mitra_price ?? 0;

            $uniqueQty = new Unique('product_discounts', 'min_qty');
            $uniqueQty = $uniqueQty->whereNull('deleted_at')->where('mitra_type', MITRA_TYPE_AGENT);

            if (!empty($values['zone_id'])) {
                $uniqueQty = $uniqueQty->where('zone_id', $values['zone_id']);
            }

            if (!empty($values['product_id'])) {
                $uniqueQty = $uniqueQty->where('product_id', $values['product_id']);
            }

            if (!empty($productDiscount)) {
                $uniqueQty = $uniqueQty->ignore($productDiscount->id, 'id');
            }

            $validator = Validator::make($values, [
                'zone_id' => ['required', "in:{$inZones}"],
                'discount_category' => ['required', "in:{$inCategories}"],
                'min_qty' => ['required', 'integer', 'gt:0', $uniqueQty],
                'discount' => ['required', 'integer', 'min:0', "lte:{$maxPrice}"],
            ], [], [
                'zone_id' => 'Zona',
                'discount_category' => 'Kategori',
                'min_qty' => 'QTY',
                'discount' => 'Diskon',
            ]);

            if ($validator->fails()) {
                $result['status'] = false;
                $result['message'] = $this->validationMessages($validator);
            }
        }

        return $result;
    }

    private function productPriceFromProduct(Product $product, array $inputs): ProductPrice|null
    {
        if (empty($inputs['zone_id'])) return null;
        return $product->prices->where('zone_id', '=', $inputs['zone_id'])->first();
    }

    public function create(Request $request)
    {
        $product = $request->product;
        $zoneId = $request->get('zone_id');
        return view('main.products.discount.form', [
            'product' => $product,
            'data' => null,
            'zones' => $this->neo->zones(true),
            'selectedZoneId' => $zoneId,
            'postUrl' => route('main.master.product.discount.store', ['product' => $product->id]),
            'modalHeader' => 'Tambah Diskon Produk',
        ]);
    }

    public function store(Request $request)
    {
        $product = $request->product;
        $values = $request->except(['_token']);

        $valid = $this->validateInput($values, $price = $this->productPriceFromProduct($product, $values));

        $responCode = 200;
        $responText = route('main.master.product.discount.index', ['product' => $product->id]);

        if (($valid['status'] === true) && !empty($price)) {
            $values['set_by'] = $request->user()->id;

            DB::beginTransaction();
            try {
                ProductDiscount::create($values);

                session([
                    'message' => 'Diskon Produk berhasil ditambahkan.',
                    'messageClass' => 'success',
                    'filter.zoneId' => $values['zone_id'],
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.');
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        $product = $request->product;
        $discount = $request->mitraDiscount;
        $zoneId = $discount->zone_id;
        return view('main.products.discount.form', [
            'product' => $product,
            'data' => $discount,
            'zones' => $this->neo->zones(true),
            'selectedZoneId' => $zoneId,
            'postUrl' => route('main.master.product.discount.update', ['product' => $product->id, 'mitraDiscount' => $discount->id]),
            'modalHeader' => 'Edit Diskon Produk',
        ]);
    }

    public function update(Request $request)
    {
        $product = $request->product;
        $discount = $request->mitraDiscount;
        $values = $request->except(['_token']);

        $valid = $this->validateInput($values, $price = $this->productPriceFromProduct($product, $values), $discount);

        $responCode = 200;
        $responText = route('main.master.product.discount.index', ['product' => $product->id]);

        if (($valid['status'] === true) && !empty($price)) {
            $values['set_by'] = $request->user()->id;
            $values['previous_id'] = $discount->id;

            DB::beginTransaction();
            try {
                $discount->delete();
                ProductDiscount::create($values);

                session([
                    'message' => 'Diskon Produk berhasil diperbarui.',
                    'messageClass' => 'success',
                    'filter.zoneId' => $values['zone_id'],
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.');
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }

    public function remove(Request $request)
    {
        $product = $request->product;
        $discount = $request->mitraDiscount;
        $zoneId = $discount->zone_id;
        return view('main.products.discount.delete', [
            'product' => $product,
            'data' => $discount,
            'zones' => $this->neo->zones(true),
            'selectedZoneId' => $zoneId,
            'postUrl' => route('main.master.product.discount.update', ['product' => $product->id, 'mitraDiscount' => $discount->id]),
            'modalHeader' => 'Hapus Diskon Produk',
        ]);
    }

    public function destroy(Request $request)
    {
        $product = $request->product;
        $discount = $request->mitraDiscount;

        $responCode = 200;
        $responText = route('main.master.product.discount.index', ['product' => $product->id]);

        DB::beginTransaction();
        try {
            $discount->delete();

            session([
                'message' => 'Diskon Produk berhasil dihapus.',
                'messageClass' => 'success',
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $responCode = 500;
            $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.');
        }

        return response($responText, $responCode);
    }
}
