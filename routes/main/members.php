<?php

use Illuminate\Support\Facades\Route;

Route::prefix('members')
    ->name('member.')
    ->group(function () {
        Route::middleware('auth.permit:main.member.index')
            ->group(function () {
                // main.member.index => main/members/
                Route::get('/', [App\Http\Controllers\Main\MemberController::class, 'index'])->name('index');
                // main.member.datatable => main/members/datatable
                Route::get('/datatable', [App\Http\Controllers\Main\MemberController::class, 'datatable'])->name('datatable');
                // main.member.detail => main/members/detail/{userMember}
                Route::get('/detail/{userMember}', [App\Http\Controllers\Main\MemberController::class, 'detail'])->name('detail');
                // download
                Route::prefix('download')
                    ->name('download.')
                    ->group(function () {
                        // main.member.crew => /main/members/crew
                        Route::get('/excel', [\App\Http\Controllers\Main\MemberController::class, 'downloadExcel'])->name('excel');
                    });

                Route::middleware('auth.permit:main.member.create')
                    ->group(function () {
                        // main.member.create => /main/members/create
                        Route::get('/create', [App\Http\Controllers\Main\MemberController::class, 'create'])->name('create');
                        // main.member.store => /main/members/store
                        Route::post('/store', [App\Http\Controllers\Main\MemberController::class, 'store'])->name('store');
                    });

                Route::middleware('auth.permit:main.member.edit')
                    ->group(function () {
                        // main.member.edit => /main/members/edit/{userMember}
                        Route::get('/edit/{userMember}', [App\Http\Controllers\Main\MemberController::class, 'edit'])->name('edit');
                        // main.member.update => /main/members/update/{userMember}
                        Route::post('/update/{userMember}', [App\Http\Controllers\Main\MemberController::class, 'update'])->name('update');
                    });
            });

        // structure
        Route::prefix('structures')
            ->name('structure.')
            ->group(function () {
                // main.member.structure.basic => /main/members/structures/basic
                Route::get('/basic', [App\Http\Controllers\Main\StructureController::class, 'basicDiagram'])
                    ->middleware('auth.permit:main.member.structure.basic')
                    ->name('basic');
                // main.member.structure.table => /main/members/structures/table
                Route::get('/table', [App\Http\Controllers\Main\StructureController::class, 'tableTreeview'])
                    ->middleware('auth.permit:main.member.structure.table')
                    ->name('table');
                // main.member.structure.tree => /main/members/structures/tree/{userTreeId}
                Route::get('/tree/{userTreeId?}', [App\Http\Controllers\Main\StructureController::class, 'genealogyTree'])
                    ->middleware('auth.permit:main.member.structure.tree')
                    ->name('tree');
            });
    });
