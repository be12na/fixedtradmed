<?php

use Illuminate\Support\Facades\Route;

Route::prefix('products')
->name('product.')
->group(function() {
    // member.product.index => /product/
    Route::get('/', [\App\Http\Controllers\Member\ProductController::class, 'index'])
        ->middleware('auth.permit:member.product.index')
        ->name('index');

    Route::prefix('stock-opname')
    ->name('stock.')
    ->middleware(['auth.permit:member.product.stock.index', 'stockopname'])
    ->group(function() {
        // member.product.stock.indexStock => /products/stock-opname/
        Route::get('/', [\App\Http\Controllers\Member\ProductController::class, 'indexStock'])->name('index');
        // member.product.stock.productStock => /products/stock-opname/product-list/{branch}
        Route::get('/product-list/{branch}', [\App\Http\Controllers\Member\ProductController::class, 'indexStock'])->name('productStock');
        // member.product.stock.input => /products/stock-opname/input/{branch}/{product}
        Route::get('/input/{branch}/{product}', [\App\Http\Controllers\Member\ProductController::class, 'inputStock'])->name('input');
        // member.product.stock.save => /products/stock-opname/save/{branch}/{product}
        Route::post('/save/{branch}/{product}', [\App\Http\Controllers\Member\ProductController::class, 'saveStock'])->name('save');
    });
});