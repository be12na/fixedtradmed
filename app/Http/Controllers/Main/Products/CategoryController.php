<?php

namespace App\Http\Controllers\Main\Products;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Unique;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::orderBy('code')->get();

        return view('main.products.product-category-index', [
            'categories' => $categories,
            'windowTitle' => 'Kategori Produk',
            'breadcrumbs' => ['Master', 'Kategori Produk']
        ]);
    }

    public function validateInput(array $values, ProductCategory $category = null)
    {
        $uniqueCode = new Unique('product_categories', 'code');
        $merek = Arr::get($values, 'merek');
        $uniqueName = new Unique('product_categories', 'name');

        if (is_null($merek)) {
            $uniqueName = $uniqueName->whereNull('merek');
        } else {
            $uniqueName = $uniqueName->where('merek', $merek);
        }

        if (!empty($category)) {
            $uniqueCode = $uniqueCode->ignore($category->id, 'id');
            $uniqueName = $uniqueName->ignore($category->id, 'id');
        }

        $validator = Validator::make($values, [
            'code' => ['required', 'string', 'max:20', $uniqueCode],
            'merek' => ['required', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:200', $uniqueName],
        ], [], [
            'code' => 'Kode Kategori',
            'merek' => 'Merek',
            'name' => 'Nama Kategori',
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

    public function create()
    {
        return view('main.products.product-category-form', [
            'data' => null,
            'postUrl' => route('main.master.product-category.store'),
            'modalHeader' => 'Tambah Kategori Produk',
        ]);
    }

    public function store(Request $request)
    {
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values);

        $responCode = 200;
        $responText = route('main.master.product-category.index');

        if ($valid['status'] === true) {
            try {
                ProductCategory::create($values);
                $valid['message'] = '';

                session([
                    'message' => 'Kategori produk berhasil ditambahkan.',
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

    public function edit(Request $request)
    {
        $category = $request->productCategory;

        return view('main.products.product-category-form', [
            'data' => $category,
            'postUrl' => route('main.master.product-category.update', ['productCategory' => $category->id]),
            'modalHeader' => 'Ubah Kategori Produk',
        ]);
    }

    public function update(Request $request)
    {
        $category = $request->productCategory;
        $values = $request->except(['_token']);
        $valid = $this->validateInput($values, $category);

        $responCode = 200;
        $responText = route('main.master.product-category.index');

        if ($valid['status'] === true) {
            try {
                $category->update($values);
                $valid['message'] = '';

                session([
                    'message' => 'Kategori produk berhasil diubah.',
                    'messageClass' => 'success'
                ]);
            } catch (\Exception $e) {
                $responCode = 500;
                $responText = view('partials.alert', [
                    'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi...',
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

    public function saveActive(Request $request)
    {
        // 
    }
}
