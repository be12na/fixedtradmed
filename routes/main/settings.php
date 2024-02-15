<?php

use Illuminate\Support\Facades\Route;

Route::prefix('settings')
    ->name('settings.')
    ->group(function () {
        // bank
        Route::prefix('bank')
            ->name('bank.')
            ->middleware('auth.permit:main.settings.bank.index')
            ->group(function () {
                // main.settings.bank.index => main/settings/bank
                Route::get('/', [App\Http\Controllers\Main\Settings\BankController::class, 'index'])->name('index');

                Route::middleware('auth.permit:main.settings.bank.create')
                    ->group(function () {
                        // main.settings.bank.create => main/settings/bank/create
                        Route::get('/create', [App\Http\Controllers\Main\Settings\BankController::class, 'create'])->name('create');
                        // main.settings.bank.store => main/settings/bank/store
                        Route::post('/store', [App\Http\Controllers\Main\Settings\BankController::class, 'store'])->name('store');
                    });

                Route::middleware('auth.permit:main.settings.bank.edit')
                    ->group(function () {
                        // main.settings.bank.edit => main/settings/bank/edit/{mainBank}
                        Route::get('/edit/{mainBank}', [App\Http\Controllers\Main\Settings\BankController::class, 'edit'])->name('edit');
                        // main.settings.bank.update => main/settings/bank/update/{mainBank}
                        Route::post('/update/{mainBank}', [App\Http\Controllers\Main\Settings\BankController::class, 'update'])->name('update');
                    });
            });

        // reward
        // Route::prefix('reward')
        //     ->name('reward.')
        //     ->middleware('auth.permit:main.settings.reward.index')
        //     ->group(function () {
        //         // main.settings.reward.index => main/settings/reward
        //         Route::get('/', [App\Http\Controllers\Main\Settings\RewardController::class, 'index'])->name('index');
        //         // main.settings.reward.datatable => main/settings/reward/datatable
        //         Route::get('/datatable', [App\Http\Controllers\Main\Settings\RewardController::class, 'datatable'])->name('datatable');

        //         Route::middleware('auth.permit:main.settings.reward.create')
        //             ->group(function () {
        //                 // main.settings.reward.create => main/settings/reward/create
        //                 Route::get('/create', [App\Http\Controllers\Main\Settings\RewardController::class, 'create'])->name('create');
        //                 // main.settings.reward.store => main/settings/reward/store
        //                 Route::post('/store', [App\Http\Controllers\Main\Settings\RewardController::class, 'store'])->name('store');
        //             });

        //         Route::middleware('auth.permit:main.settings.reward.edit')
        //             ->group(function () {
        //                 // main.settings.reward.edit => main/settings/reward/edit/{mainReward}
        //                 Route::get('/edit/{mainReward}', [App\Http\Controllers\Main\Settings\RewardController::class, 'edit'])->name('edit');
        //                 // main.settings.reward.update => main/settings/reward/update/{mainReward}
        //                 Route::post('/update/{mainReward}', [App\Http\Controllers\Main\Settings\RewardController::class, 'update'])->name('update');
        //             });
        //     });

        // // bonus
        // Route::prefix('bonus')
        //     ->name('bonus.')
        //     ->middleware('auth.permit:main.settings.bonus.index')
        //     ->group(function () {
        //         // main.settings.bonus.index => main/settings/bonus
        //         Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'index'])->name('index');
        //         // bonus royalty
        //         Route::prefix('royalty')
        //             ->name('royalty.')
        //             ->middleware('auth.permit:main.settings.bonus.royalty.index')
        //             ->group(function () {
        //                 // main.settings.bonus.royalty.index => main/settings/bonus/royalty
        //                 Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'indexRoyalty'])->name('index');

        //                 Route::middleware('auth.permit:main.settings.bonus.royalty.edit')
        //                     ->group(function () {
        //                         // main.settings.bonus.royalty.edit => main/settings/bonus/royalty/edit
        //                         Route::get('/edit', [App\Http\Controllers\Main\Settings\BonusController::class, 'editRoyalty'])->name('edit');
        //                         // main.settings.bonus.royalty.update => main/settings/bonus/royalty/update
        //                         Route::post('/update', [App\Http\Controllers\Main\Settings\BonusController::class, 'updateRoyalty'])->name('update');
        //                     });
        //             });

        //         // bonus override
        //         Route::prefix('override')
        //             ->name('override.')
        //             ->middleware('auth.permit:main.settings.bonus.override.index')
        //             ->group(function () {
        //                 // main.settings.bonus.override.index => main/settings/bonus/override
        //                 Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'indexOverride'])->name('index');

        //                 Route::middleware('auth.permit:main.settings.bonus.override.edit')
        //                     ->group(function () {
        //                         // main.settings.bonus.override.edit => main/settings/bonus/override/edit
        //                         Route::get('/edit', [App\Http\Controllers\Main\Settings\BonusController::class, 'editOverride'])->name('edit');
        //                         // main.settings.bonus.override.update => main/settings/bonus/override/update
        //                         Route::post('/update', [App\Http\Controllers\Main\Settings\BonusController::class, 'updateOverride'])->name('update');
        //                     });
        //             });

        //         // bonus team
        //         Route::prefix('team')
        //             ->name('team.')
        //             ->middleware('auth.permit:main.settings.bonus.team.index')
        //             ->group(function () {
        //                 // main.settings.bonus.team.index => main/settings/bonus/team
        //                 Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'indexBonusTeam'])->name('index');

        //                 Route::middleware('auth.permit:main.settings.bonus.team.edit')
        //                     ->group(function () {
        //                         // main.settings.bonus.team.edit => main/settings/bonus/team/edit
        //                         Route::get('/edit', [App\Http\Controllers\Main\Settings\BonusController::class, 'editBonusTeam'])->name('edit');
        //                         // main.settings.bonus.team.update => main/settings/bonus/team/update
        //                         Route::post('/update', [App\Http\Controllers\Main\Settings\BonusController::class, 'updateBonusTeam'])->name('update');
        //                     });
        //             });

        //         // bonus sell
        //         Route::prefix('sell')
        //             ->name('sell.')
        //             ->middleware('auth.permit:main.settings.bonus.sell.index')
        //             ->group(function () {
        //                 // main.settings.bonus.sell.index => main/settings/bonus/sell
        //                 Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'indexBonusSell'])->name('index');

        //                 Route::middleware('auth.permit:main.settings.bonus.sell.edit')
        //                     ->group(function () {
        //                         // main.settings.bonus.sell.edit => main/settings/bonus/sell/edit
        //                         Route::get('/edit', [App\Http\Controllers\Main\Settings\BonusController::class, 'editBonusSell'])->name('edit');
        //                         // main.settings.bonus.sell.update => main/settings/bonus/sell/update
        //                         Route::post('/update', [App\Http\Controllers\Main\Settings\BonusController::class, 'updateBonusSell'])->name('update');
        //                     });
        //             });

        //         // bonus mgr direct Mitra Premium
        //         Route::prefix('mitra-premium/shopping/{settingBonusMitraShoppingTarget}')
        //             ->name('mitraPremiumShopping.')
        //             ->middleware('auth.permit:main.settings.bonus.mitraPremiumShopping.index')
        //             ->group(function () {
        //                 // main.settings.bonus.mitraPremiumShopping.index => main/settings/bonus/mitra-premium/shopping/{settingBonusMitraShoppingTarget}
        //                 Route::get('/', [App\Http\Controllers\Main\Settings\BonusController::class, 'indexBonusMitraPremiumShopping'])->name('index');

        //                 Route::middleware('auth.permit:main.settings.bonus.mitraPremiumShopping.edit')
        //                     ->group(function () {
        //                         // main.settings.bonus.mitraPremiumShopping.edit => main/settings/bonus/mitra-premium/shopping/{settingBonusMitraShoppingTarget}/edit
        //                         Route::get('/edit', [App\Http\Controllers\Main\Settings\BonusController::class, 'editBonusMitraPremiumShopping'])->name('edit');
        //                         // main.settings.bonus.mitraPremiumShopping.update => main/settings/bonus/mitra-premium/shopping/{settingBonusMitraShoppingTarget}/update
        //                         Route::post('/update', [App\Http\Controllers\Main\Settings\BonusController::class, 'updateBonusMitraPremiumShopping'])->name('update');
        //                     });
        //             });
        //     });

        // mitra
        // Route::prefix('mitra')
        //     ->name('mitra.')
        //     ->middleware('auth.permit:main.settings.mitra.index')
        //     ->group(function () {
        //         // main.settings.mitra.index => main/settings/mitra
        //         Route::get('/', [App\Http\Controllers\Main\Settings\MitraController::class, 'index'])->name('index');

        //         Route::prefix('purchase')
        //             ->name('purchase.')
        //             ->group(function () {
        //                 // purchase discount
        //                 Route::prefix('discount')
        //                     ->name('discount.')
        //                     ->middleware('auth.permit:main.settings.mitra.purchase.discount.index')
        //                     ->group(function () {
        //                         // main.settings.mitra.purchase.discount.index => main/settings/mitra/purchase/discount
        //                         Route::get('/', [App\Http\Controllers\Main\Settings\MitraController::class, 'indexDiscount'])->name('index');

        //                         Route::middleware('auth.permit:main.settings.mitra.purchase.discount.edit')
        //                             ->group(function () {
        //                                 // main.settings.mitra.purchase.discount.edit => main/settings/mitra/purchase/discount/edit
        //                                 Route::get('/edit', [App\Http\Controllers\Main\Settings\MitraController::class, 'editDiscount'])->name('edit');
        //                                 // main.settings.mitra.purchase.discount.update => main/settings/mitra/purchase/discount/update
        //                                 Route::post('/update', [App\Http\Controllers\Main\Settings\MitraController::class, 'updateDiscount'])->name('update');
        //                             });
        //                     });

        //                 // purchase cashback
        //                 Route::prefix('cashback')
        //                     ->name('cashback.')
        //                     ->middleware('auth.permit:main.settings.mitra.purchase.cashback.index')
        //                     ->group(function () {
        //                         // main.settings.mitra.purchase.cashback.index => main/settings/mitra/purchase/cashback
        //                         Route::get('/', [App\Http\Controllers\Main\Settings\MitraController::class, 'indexCashback'])->name('index');

        //                         Route::middleware('auth.permit:main.settings.mitra.purchase.cashback.edit')
        //                             ->group(function () {
        //                                 // main.settings.mitra.purchase.cashback.edit => main/settings/mitra/purchase/cashback/edit
        //                                 Route::get('/edit', [App\Http\Controllers\Main\Settings\MitraController::class, 'editCashback'])->name('edit');
        //                                 // main.settings.mitra.purchase.cashback.update => main/settings/mitra/purchase/cashback/update
        //                                 Route::post('/update', [App\Http\Controllers\Main\Settings\MitraController::class, 'updateCashback'])->name('update');
        //                             });
        //                     });
        //             });
        //     });

        // administrator
        Route::prefix('admin')
            ->name('admin.')
            ->middleware('auth.permit:main.settings.admin.index')
            ->group(function () {
                // main.settings.admin.index => main/settings/admin
                Route::get('/', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'index'])->name('index');
                // main.settings.admin.datatable => main/settings/admin/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'datatable'])->name('datatable');

                Route::middleware('auth.permit:main.settings.admin.create')
                    ->group(function () {
                        // main.settings.admin.create => main/settings/admin/create
                        Route::get('/create', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'create'])->name('create');
                        // main.settings.admin.store => main/settings/admin/store
                        Route::post('/store', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'store'])->name('store');
                    });

                Route::middleware('auth.permit:main.settings.admin.edit')
                    ->group(function () {
                        // main.settings.admin.edit => main/settings/admin/edit/{mainAdmin}
                        Route::get('/edit/{mainAdmin}', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'edit'])->name('edit');
                        // main.settings.admin.update => main/settings/admin/update/{mainAdmin}
                        Route::post('/update/{mainAdmin}', [App\Http\Controllers\Main\Settings\UserAdminController::class, 'update'])->name('update');
                    });
            });

        // role group administrator
        Route::prefix('roles')
            ->name('roles.')
            ->middleware('auth.permit:main.settings.roles.index')
            ->group(function () {
                // main.settings.roles.index => main/settings/roles
                Route::get('/', [App\Http\Controllers\Main\Settings\RolesController::class, 'index'])->name('index');
                // main.settings.roles.form => main/settings/roles/form
                Route::get('/form', [App\Http\Controllers\Main\Settings\RolesController::class, 'getForm'])->name('form');

                Route::middleware('auth.permit:main.settings.roles.update')
                    ->group(function () {
                        // main.settings.roles.update => main/settings/roles/update
                        Route::post('/update', [App\Http\Controllers\Main\Settings\RolesController::class, 'update'])->name('update');
                    });
            });

        // quota paket
        Route::prefix('quota')
            ->name('quota.')
            ->middleware('auth.permit:main.settings.quota.index')
            ->group(function () {
                // main.settings.quota.index => main/settings/quota
                Route::get('/', [App\Http\Controllers\Main\Settings\QuotaController::class, 'index'])->name('index');

                Route::middleware('auth.permit:main.settings.quota.edit')
                    ->group(function () {
                        // main.settings.quota.edit => main/settings/quota/edit/{quotaPackage}
                        Route::get('/edit/{quotaPackage}', [App\Http\Controllers\Main\Settings\QuotaController::class, 'edit'])->name('edit');
                        // main.settings.quota.update => main/settings/quota/update/{quotaPackage}
                        Route::post('/update/{quotaPackage}', [App\Http\Controllers\Main\Settings\QuotaController::class, 'edit'])->name('update');
                    });
            });
    });
