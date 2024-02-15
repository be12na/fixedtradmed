<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $zone = $request->user()->user_zone;
        $fieldZone = 'harga_a'; //($zone == ZONE_EAST) ? 'harga_d' : 'harga_c';

        $categories = ProductCategory::byActive()
            ->whereHas('products', function ($has) use ($fieldZone) {
                return $has->byActive()->byPublished(true)
                    ->where($fieldZone, '>', 0);
            })
            ->with(['products' => function ($with) use ($fieldZone) {
                return $with->byActive()->byPublished(true)
                    ->where($fieldZone, '>', 0)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return view('mitra.products.index', [
            'categories' => $categories,
            'zone' => $zone,
            'windowTitle' => 'Daftar Produk',
            'breadcrumbs' => ['Produk', 'Daftar']
        ]);
    }
}
