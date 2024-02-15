<?php

namespace App\Http\Controllers\Mitra\Market;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function index(Request $request)
    {
        $mitra = $request->mitraStore;
        $products = Product::byActive()
            ->byPublished()
            ->orderBy('name')
            ->with(['category'])
            ->get();

        $homePage = $request->url();

        return view('public.mitra-store', [
            'mitra' => $mitra,
            'products' => $products,
            'homePage' => $homePage,
        ]);
    }
}
