<?php

namespace App\Http\Controllers\Main\Products;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\ProductPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonusController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $product = $request->product;

        return view('main.products.bonus.index', [
            'product' => $product,
            'windowTitle' => 'Daftar Diskon Produk',
            'breadcrumbs' => ['Master', 'Produk', 'Bonus']
        ]);
    }

    private function validateInput(array &$values, ProductPrice $productPrice)
    {
        $values = array_merge([
            'zone_id' => $productPrice->zone_id,
            'product_id' => $productPrice->product_id,
            'normal_price' => $productPrice->normal_price,
            'normal_retail_price' => $productPrice->normal_retail_price,
            'mitra_price' => $productPrice->mitra_price,
            'mitra_retail_price' => $productPrice->mitra_retail_price,
        ], $values);

        $validator = Validator::make($values, [
            'mitra_basic_bonus' => 'required|integer|min:0',
            'mitra_premium_bonus' => 'required|integer|min:0',
            'distributor_bonus' => 'required|integer|min:0',
        ]);

        $result = ['status' => true, 'message' => ''];

        if ($validator->fails()) {
            $result['status'] = false;
            $result['message'] = $this->validationMessages($validator);
        }

        return $result;
    }

    public function edit(Request $request)
    {
        $product = $request->product;
        $zone = $request->zone;
        $zoneId = $zone->id;
        $price = $product->prices()->where('zone_id', '=', $zoneId)->first();

        if (empty($price)) return ajaxError('Harga produk belum ditentukan.', 404);

        return view('main.products.bonus.form', [
            'product' => $product,
            'data' => $price,
            'zone' => $zone,
            'postUrl' => route('main.master.product.bonus.update', ['product' => $product->id, 'zone' => $zoneId]),
            'modalHeader' => 'Edit Bonus Produk',
        ]);
    }

    public function update(Request $request)
    {
        $product = $request->product;
        $zone = $request->zone;
        $zoneId = $zone->id;
        $productPrice = $product->prices()->where('zone_id', '=', $zoneId)->first();

        $responCode = 200;
        $responText = route('main.master.product.bonus.index', ['product' => $product->id]);

        if (!empty($productPrice)) {
            $values = $request->except(['_token']);
            $valid = $this->validateInput($values, $productPrice);

            if ($valid['status'] === true) {
                $values['created_by'] = $request->user()->id;

                DB::beginTransaction();
                try {
                    $productPrice->delete();
                    ProductPrice::create($values);

                    session([
                        'message' => 'Bonus Produk berhasil disimpan.',
                        'messageClass' => 'success',
                    ]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $responCode = 500;
                    $responText = $this->validationMessages('Telah terjadi kesalahan pada server. Silahkan coba lagi.' . (isLive() ? '' : ' ' . $e->getMessage()));
                }
            } else {
                $responCode = 400;
                $responText = $valid['message'];
            }
        } else {
            $responCode = 404;
            $responText = $this->validationMessages('Harga produk belum ditentukan.');
        }

        return response($responText, $responCode);
    }
}
