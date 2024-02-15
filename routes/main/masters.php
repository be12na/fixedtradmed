<?php

use Illuminate\Support\Facades\Route;

// menu master
Route::prefix('masters')
    ->name('master.')
    ->group(function () {
        // product category
        Route::prefix('product-category')
            ->name('product-category.')
            ->middleware('auth.permit:main.master.product-category.index')
            ->group(function () {
                // main.master.product-category.index => /main/master/product-category
                Route::get('/', [App\Http\Controllers\Main\Products\CategoryController::class, 'index'])->name('index');

                Route::middleware('auth.permit:main.master.product-category.create')
                    ->group(function () {
                        // main.master.product-category.create => /main/master/product-category/create
                        Route::get('/create', [App\Http\Controllers\Main\Products\CategoryController::class, 'create'])->name('create');
                        // main.master.product-category.store => /main/master/product-category/store
                        Route::post('/store', [App\Http\Controllers\Main\Products\CategoryController::class, 'store'])->name('store');
                    });
                Route::middleware('auth.permit:main.master.product-category.edit')
                    ->group(function () {
                        // main.master.product-category.edit => /main/master/product-category/edit/{productCategory}
                        Route::get('/edit/{productCategory}', [App\Http\Controllers\Main\Products\CategoryController::class, 'edit'])->name('edit');
                        // main.master.product-category.update => /main/master/product-category/update/{productCategory}
                        Route::post('/update/{productCategory}', [App\Http\Controllers\Main\Products\CategoryController::class, 'update'])->name('update');
                    });
            });

        // product
        Route::prefix('/products')
            ->name('product.')
            ->middleware('auth.permit:main.master.product.index')
            ->group(function () {
                // main.master.product.index => /main/master/product
                Route::get('/', [App\Http\Controllers\Main\Products\ProductController::class, 'index'])->name('index');
                // main.master.product.byCategory => /main/master/product/by-category/{categoryId?}
                Route::get('/by-category/{categoryId?}', [App\Http\Controllers\Main\Products\ProductController::class, 'index'])->name('byCategory');

                Route::middleware('auth.permit:main.master.product.create')
                    ->group(function () {
                        // main.master.product.create => /main/master/product/create/{categoryId?}
                        Route::get('/create/{categoryId?}', [App\Http\Controllers\Main\Products\ProductController::class, 'create'])->name('create');
                        // main.master.product.store => /main/master/product/store
                        Route::post('/store', [App\Http\Controllers\Main\Products\ProductController::class, 'store'])->name('store');
                    });
                Route::middleware('auth.permit:main.master.product.edit')
                    ->group(function () {
                        // main.master.product.edit => /main/master/product/edit/{product}
                        Route::get('/edit/{product}', [App\Http\Controllers\Main\Products\ProductController::class, 'edit'])->name('edit');
                        // main.master.product.update => /main/master/product/update/{product}
                        Route::post('/update/{product}', [App\Http\Controllers\Main\Products\ProductController::class, 'update'])->name('update');
                    });
                // main.master.product.toggle => /main/master/product/toggle/{product}
                // Route::post('/toggle/{product}', [App\Http\Controllers\Main\Products\ProductController::class, 'toggleActive'])->name('toggle');

                // // BONUS
                // Route::prefix('bonus/{product}')
                //     ->name('bonus.')
                //     ->middleware('auth.permit:main.master.product.bonus.index')
                //     ->group(function () {
                //         // main.master.product.bonus.index => /main/master/product/bonus/{product}
                //         Route::get('/', [App\Http\Controllers\Main\Products\BonusController::class, 'index'])->name('index');

                //         Route::middleware('auth.permit:main.master.product.bonus.edit')
                //             ->group(function () {
                //                 // main.master.product.bonus.edit => /main/master/product/bonus/{product}/edit/{zone}
                //                 Route::get('/edit/{zone}', [App\Http\Controllers\Main\Products\BonusController::class, 'edit'])->name('edit');
                //                 // main.master.product.bonus.update => /main/master/product/bonus/{product}/update/{zone}
                //                 Route::post('/update/{zone}', [App\Http\Controllers\Main\Products\BonusController::class, 'update'])->name('update');
                //             });
                //     });

                // // DISCOUNT
                // Route::prefix('discount/{product}')
                //     ->name('discount.')
                //     ->middleware('auth.permit:main.master.product.discount.index')
                //     ->group(function () {
                //         // main.master.product.discount.index => /main/master/product/discount/{product}
                //         Route::get('/', [App\Http\Controllers\Main\Products\DiscountController::class, 'index'])->name('index');
                //         // main.master.product.discount.datatable => /main/master/product/discount/{product}/datatable
                //         Route::get('/datatable', [App\Http\Controllers\Main\Products\DiscountController::class, 'datatable'])->name('datatable');

                //         // create
                //         Route::middleware('auth.permit:main.master.product.discount.create')
                //             ->group(function () {
                //                 // main.master.product.discount.create => /main/master/product/discount/{product}/create
                //                 Route::get('/create', [App\Http\Controllers\Main\Products\DiscountController::class, 'create'])->name('create');
                //                 // main.master.product.discount.store => /main/master/product/discount/{product}/store
                //                 Route::post('/store', [App\Http\Controllers\Main\Products\DiscountController::class, 'store'])->name('store');
                //             });
                //         // edit
                //         Route::middleware('auth.permit:main.master.product.discount.edit')
                //             ->group(function () {
                //                 // main.master.product.discount.edit => /main/master/product/discount/{product}/edit/{mitraDiscount}
                //                 Route::get('/edit/{mitraDiscount}', [App\Http\Controllers\Main\Products\DiscountController::class, 'edit'])->name('edit');
                //                 // main.master.product.discount.update => /main/master/product/discount/{product}/update/{mitraDiscount}
                //                 Route::post('/update/{mitraDiscount}', [App\Http\Controllers\Main\Products\DiscountController::class, 'update'])->name('update');
                //             });
                //         // delete / remove
                //         Route::middleware('auth.permit:main.master.product.discount.remove')
                //             ->group(function () {
                //                 // main.master.product.discount.remove => /main/master/product/discount/{product}/remove/{mitraDiscount}
                //                 Route::get('/remove/{mitraDiscount}', [App\Http\Controllers\Main\Products\DiscountController::class, 'remove'])->name('remove');
                //                 // main.master.product.discount.destroy => /main/master/product/discount/{product}/destroy/{mitraDiscount}
                //                 Route::post('/destroy/{mitraDiscount}', [App\Http\Controllers\Main\Products\DiscountController::class, 'destroy'])->name('destroy');
                //             });
                //     });

                // // REWARD
                // Route::prefix('reward/{product}')
                //     ->name('reward.')
                //     ->middleware('auth.permit:main.master.product.reward.index')
                //     ->group(function () {
                //         // main.master.product.reward.index => /main/master/product/reward/{product}
                //         Route::get('/', [App\Http\Controllers\Main\Products\RewardController::class, 'index'])->name('index');
                //         // main.master.product.reward.datatable => /main/master/product/reward/{product}/datatable
                //         Route::get('/datatable', [App\Http\Controllers\Main\Products\RewardController::class, 'datatable'])->name('datatable');

                //         Route::middleware('auth.permit:main.master.product.reward.create')
                //             ->group(function () {
                //                 // main.master.product.reward.create => /main/master/product/reward/{product}/create
                //                 Route::get('/create', [App\Http\Controllers\Main\Products\RewardController::class, 'create'])->name('create');
                //                 // main.master.product.reward.store => /main/master/product/reward/{product}/store
                //                 Route::post('/store', [App\Http\Controllers\Main\Products\RewardController::class, 'store'])->name('store');
                //             });
                //         Route::middleware('auth.permit:main.master.product.reward.edit')
                //             ->group(function () {
                //                 // main.master.product.reward.edit => /main/master/product/reward/{product}/edit/{mitraReward}
                //                 Route::get('/edit/{mitraReward}', [App\Http\Controllers\Main\Products\RewardController::class, 'edit'])->name('edit');
                //                 // main.master.product.reward.update => /main/master/product/reward/{product}/update/{mitraReward}
                //                 Route::post('/update/{mitraReward}', [App\Http\Controllers\Main\Products\RewardController::class, 'update'])->name('update');
                //             });
                //     });
            });
    });
