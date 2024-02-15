<?php

namespace App\Http\Controllers\Main\Products;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class ProductController extends Controller
{
    private Neo $neo;
    private bool $isAppV2;

    public function __construct()
    {
        $this->neo = app('neo');
        $this->isAppV2 = isAppV2();
    }

    public function index(Request $request)
    {
        if ($request->route()->getName() == 'main.master.product.byCategory') {
            $categoryId = $request->categoryId ?? -1;
            $categories = ProductCategory::orderBy('name')->orderBy('merek')->with(['products' => function ($query) {
                return $query->orderBy('name');
            }]);

            if ($categoryId > -1) {
                $categories = $categories->byId($categoryId);
            }

            $categories = $categories->get();

            session(['product.categoryId' => $categoryId]);

            return view('main.products.product-list', [
                'categories' => $categories,
                'categoryId' => $categoryId,
                'isAppV2' => $this->isAppV2,
            ]);
        }

        $currentCategoryId = session('product.categoryId') ?? -1;

        return view('main.products.product-index', [
            'currentCategoryId' => $currentCategoryId,
            'categories' => ProductCategory::byActive()->orderBy('merek')->orderBy('name')->get(),
            'isAppV2' => $this->isAppV2,
            'windowTitle' => 'Daftar Produk',
            'breadcrumbs' => ['Master', 'Produk']
        ]);
    }

    public function validateInput(array &$values, Product $product = null)
    {
        $kategoriId = $values['product_category_id'];
        $uniqueCode = new Unique('products', 'code');
        // $uniqueRange = new Unique('products', 'package_range');
        // $uniqueRange = $uniqueRange
        //     ->where('product_category_id', $kategoriId);

        $uniqueName = new Unique('products', 'name');
        $uniqueName = $uniqueName
            ->where('product_category_id', $kategoriId)
            ->where('satuan', $values['satuan']);

        if (!empty($product)) {
            $uniqueCode = $uniqueCode->ignore($product->id, 'id');
            // $uniqueRange = $uniqueRange->ignore($product->id, 'id');
            $uniqueName = $uniqueName->ignore($product->id, 'id');
        }

        $satuanIds = implode(',', array_keys(PRODUCT_UNITS));

        $rules = [
            'product_category_id' => ['required', 'exists:product_categories,id'],
            'code' => ['required', 'string', 'max:30', $uniqueCode],
            'name' => ['required', 'string', 'max:200', $uniqueName],
            'satuan' => ['required', "in:{$satuanIds}"],
            // 'bonus_sponsor' => ['required', 'integer', 'min:0'],
            // 'bonus_sponsor_ro' => ['required', 'integer', 'min:0'],
            // 'bonus_cashback' => ['required', 'integer', 'min:0'],
            // 'bonus_cashback_condition' => ['required', 'integer', 'min:0'],
            // 'bonus_cashback_ro' => ['required', 'integer', 'min:0'],
            // 'bonus_cashback_ro_condition' => ['required', 'integer', 'min:0'],
            // 'package_range' => ['required', 'integer', $uniqueRange],
        ];

        $satuan = $values['satuan'];
        if ($satuan == PRODUCT_UNIT_BOX) {
            $rules['isi'] = ['required', 'integer', 'gt:1'];
            $values['satuan_isi'] = PRODUCT_UNIT_PCS;
        } else {
            $values['isi'] = 1;
            $values['satuan_isi'] = null;
        }

        $values['is_publish'] = isset($values['is_publish']) ? true : false;

        $otherAttributes = [];

        $hargaRules = [
            'eceran_d' => ['required', 'integer', 'min:0'], // hpp
            'harga_a' => ['required', 'integer', 'min:0'], // harga satuan
            'harga_b' => ['required', 'integer', 'min:0'], // harga dropshipper
            'harga_c' => ['required', 'integer', 'min:0'], // harga reseller
            'harga_d' => ['required', 'integer', 'min:0'], // harga distributor
        ];

        $rules = array_merge($rules, $hargaRules);
        $rules['image'] = [Rule::requiredIf(empty($product)), 'image', 'mimetypes:image/jpeg,image/png', 'max:512'];

        $validator = Validator::make($values, $rules, [
            'name.unique' => str_replace(':attribute', ':attribute dengan kategori, nama dan satuan tersebut', __('validation.unique')),
            // 'package_range.unique' => str_replace(':attribute', ':attribute dengan kategori tersebut', __('validation.unique')),
            'image.required' => 'Gambar Produk harus disertakan.',
        ], array_merge([
            'product_category_id' => 'Kategori Produk',
            'code' => 'Kode Produk',
            'name' => 'Nama Produk',
            // 'package_range' => 'No. Urut',
            'satuan' => 'Satuan Produk',
            'isi' => 'Isi / Box',
            'harga_a' => 'Harga Satuan',
            'harga_b' => 'Harga Dropshipper',
            'harga_c' => 'Harga Reseller',
            'harga_d' => 'Harga Distributor',
            'eceran_d' => 'Harga Pokok Penjualan',
            // 'bonus_sponsor' => 'Bonus Sponsor',
            // 'bonus_sponsor_ro' => 'Bonus Sponsor RO',
            // 'bonus_cashback' => 'Bonus Cashback',
            // 'bonus_cashback_condition' => 'Syarat Cashback',
            // 'bonus_cashback_ro' => 'Bonus Poin RO',
            // 'bonus_cashback_ro_condition' => 'Syarat Poin RO',
            'image' => 'Gambar Produk',
        ], $otherAttributes));

        $result = ['status' => true, 'message' => ''];
        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $this->validationMessages($validator);
        }

        return $result;
    }

    public function create(Request $request)
    {
        $categories = ProductCategory::byActive()->orderBy('merek')->orderBy('name')->get();
        $selectedCategoryId = $request->categoryId ?? 0;

        return view('main.products.product-form', [
            'data' => null,
            'categories' => $categories,
            'isAppV2' => $this->isAppV2,
            'selectedCategoryId' => $selectedCategoryId,
            'postUrl' => route('main.master.product.store'),
            'modalHeader' => 'Tambah Produk',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.master.product.index');

        if ($valid['status'] === true) {
            $user = $request->user();

            DB::beginTransaction();
            try {
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $ext = $file->extension();
                    $filename = uniqid() . ".{$ext}";
                    $file->storePubliclyAs('/', $filename, Product::IMAGE_DISK);
                    $values['image'] = $filename;
                }

                $product = Product::create($values);

                if (isset($values['product_prices'])) {
                    foreach ($values['product_prices'] as $priceValue) {
                        $priceValue['created_by'] = $user->id;
                        $product->prices()->create($priceValue);
                    }
                }

                $valid['message'] = '';

                session([
                    'message' => 'Produk berhasil ditambahkan.',
                    'messageClass' => 'success'
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi. ' . (!isLive() ? $e->getMessage() : ''),
                    'messageClass' => 'danger'
                ])->render();
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }

    public function edit(Request $request)
    {
        $categories = ProductCategory::byActive()->orderBy('merek')->orderBy('name')->get();
        $product = $request->product;
        $selectedCategoryId = $product->product_category_id;

        return view('main.products.product-form', [
            'data' => $product,
            'categories' => $categories,
            'isAppV2' => $this->isAppV2,
            'selectedCategoryId' => $selectedCategoryId,
            'postUrl' => route('main.master.product.update', ['product' => $product->id]),
            'modalHeader' => 'Edit Produk',
        ]);
    }

    public function update(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $product = $request->product);

        $responCode = 200;
        $responText = route('main.master.product.index');

        if ($valid['status'] === true) {
            $user = $request->user();
            $prices = $product->prices;
            if (isset($values['product_prices']) && $this->isAppV2 && $prices->isNotEmpty()) {
                $renewPrice = [];
                foreach ($values['product_prices'] as $priceValue) {
                    $prodPrice = $prices->where('zone_id', '=', $priceValue['zone_id'])->first();
                    if (!empty($prodPrice)) {
                        $renewPrice[] = array_merge($priceValue, [
                            'mitra_basic_bonus' => $prodPrice->mitra_basic_bonus,
                            'mitra_premium_bonus' => $prodPrice->mitra_premium_bonus,
                            'distributor_bonus' => $prodPrice->distributor_bonus,
                        ]);
                    } else {
                        $renewPrice[] = $priceValue;
                    }
                }

                $values['product_prices'] = $renewPrice;
            }

            DB::beginTransaction();
            try {
                $uploaded = false;
                $oldImage = $product->image;
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    $ext = $file->extension();
                    $filename = uniqid() . ".{$ext}";
                    $file->storePubliclyAs('/', $filename, Product::IMAGE_DISK);
                    $values['image'] = $filename;
                    $uploaded = true;
                }

                $product->update($values);

                if (isset($values['product_prices']) && $this->isAppV2) {
                    foreach ($prices as $price) {
                        $price->delete();
                    }
                    foreach ($values['product_prices'] as $priceValue) {
                        $priceValue['created_by'] = $user->id;
                        $product->prices()->create($priceValue);
                    }
                }

                $valid['message'] = '';

                session([
                    'message' => 'Produk berhasil diperbarui.',
                    'messageClass' => 'success'
                ]);

                if (($uploaded === true) && !empty($oldImage)) Storage::disk(Product::IMAGE_DISK)->delete($oldImage);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi. ' . (!isLive() ? $e->getMessage() : ''),
                    'messageClass' => 'danger'
                ])->render();
            }
        } else {
            $responCode = 400;
            $responText = $valid['message'];
        }

        return response($responText, $responCode);
    }

    public function toggleActive(Request $request)
    {
        //
    }
}
