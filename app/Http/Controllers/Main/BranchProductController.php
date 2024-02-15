<?php

namespace App\Http\Controllers\Main;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Models\BranchSale;
use App\Models\BranchStock;
use App\Models\ProductCategory;
use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\HtmlString;
use Spatie\SimpleExcel\SimpleExcelWriter;

class BranchProductController extends Controller
{
    private Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $branches = Branch::orderBy('name')->get();
        $currentBranchId = session('filter.branchId') ?? -1;

        return view('main.branches.product-index', [
            'currentBranchId' => $currentBranchId,
            'branches' => $branches,
            'windowTitle' => 'Produk Kantor Cabang',
            'breadcrumbs' => ['Kantor Cabang', 'Produk']
        ]);
    }

    public function datatable(Request $request)
    {
        $branchId = intval($request->get('branch_id', -1));
        $time = strtotime(date('Y-m-d'));

        $categories = ProductCategory::with(['products' => function ($p) {
            return $p->orderBy('name');
        }])
            ->whereHas('products')
            ->orderBy('name')->get();

        $branches = Branch::with([
            'productsFull',
        ]);

        $query = DB::table('branches')->selectRaw('id, name')->orderBy('name');

        if ($branchId > 0) {
            $branches = $branches->byId($branchId);
            $query = $query->where('id', '=', $branchId);
        }

        session(['filter.branchId' => $branchId]);
        $weekRanges = [
            $time, // minggu sekarang
            strtotime('-1 week', $time), // minggu lalu
            strtotime('-2 week', $time), // 2 minggu lalu
            strtotime('-3 week', $time), // 3 minggu lalu
        ];

        return datatables()->query($query)
            ->editColumn('name', function ($row) use ($branches, $categories, $weekRanges) {
                $branch = $branches->where('id', '=', $row->id)->first();

                return new HtmlString(view('main.branches.product-stock', [
                    'branch' => $branch,
                    'categories' => $categories,
                    'weekRanges' => $weekRanges,
                ])->render());
            })
            ->escapeColumns(['name'])
            ->toJson();
    }

    public function editStock(Request $request)
    {
        $product = $request->product;
        $branch = $request->branch;
        $branchProducts = $branch->products;
        $branchProduct = optional($branchProducts->where('product_id', '=', $product->id)->first());
        // $branchStock = optional($branchProduct->currentStock);
        $branchStock = $branchProduct->currentStock;

        $inputModes = STOCK_FLAG_ADMIN;
        // if (!empty($branchStock->id)) {
        if (!empty($branchStock)) {
            if ($branchStock->total_stock == 0) {
                if ($branchStock->diff_stock != 0) {
                    $inputModes = STOCK_FLAG_EDIT;
                } else {
                    $inputModes = STOCK_FLAG_PLUS;
                }
            } else {
                $inputModes = [STOCK_FLAG_PLUS => 'Tambah persediaan', STOCK_FLAG_MINUS => 'Kurangi persediaan'];
            }
        } else {
            // $prevousWeek = $this->neo->dateRangeStockOpname(strtotime('-1 week', time()), 'Y-m-d');
            // $branchStock = optional($branchProduct->previousStock);
            // $sales = BranchSale::byBetweenDate($prevousWeek->start, $prevousWeek->end)
            //     ->inBranches([$branch->id])
            //     ->whereHas('products', function ($spr) use ($product) {
            //         return $spr->byProduct($product);
            //     })
            //     ->with(['products' => function ($spr) use ($product) {
            //         return $spr->byProduct($product);
            //     }])
            //     ->get();

            // $branchStock->output_stock = $sales->pluck('product_quantity')->map(function ($row) {
            //     return ['qty' => $row->sum('quantity')];
            // })->sum('qty');

            // $branchStock->rest_stock = ($branchStock->total_stock ?? 0) - $branchStock->output_stock;

            $branchStock = optional($branchProduct->previousStock);
        }

        $isProductBox = ($product->satuan == PRODUCT_UNIT_BOX);
        $realStock = optional($branchStock->real_stock);

        if ($inputModes === STOCK_FLAG_ADMIN) {
            $branchStock->rest_stock = $isProductBox ? ($realStock->boxBalance ?? 0) : ($realStock->pcsBalance ?? 0);
        }

        $lastStock = (object) [
            'id' => $branchStock->id,
            'last_stock' => $branchStock->last_stock ?? 0,
            'output_stock' => $branchStock->output_stock ?? 0,
            'rest_stock' => $branchStock->rest_stock ?? 0,
            'input_manager' => $branchStock->input_manager ?? 0,
            'diff_stock' => $branchStock->diff_stock ?? 0,
            'input_admin' => $branchStock->input_admin ?? 0,
            'total_stock' => $isProductBox ? ($realStock->boxStock ?? 0) : ($realStock->pcsStock ?? 0),
            'total_output' => $isProductBox ? ($realStock->boxOut ?? 0) : ($realStock->pcsOut ?? 0),
            'total_balance' => $isProductBox ? ($realStock->boxBalance ?? 0) : ($realStock->pcsBalance ?? 0),
        ];

        return view('main.branches.product-form-stock', [
            'branch' => $branch,
            'product' => $product,
            'lastStock' => $lastStock,
            'inputModes' => $inputModes,
            'postUrl' => route('main.branch.product.update-stock', ['branch' => $branch->id, 'product' => $product->id]),
        ]);
    }

    public function updateStock(Request $request)
    {
        $values = $request->only(['product_id', 'branch_id', 'input_mode', 'input_value']);

        $validator = Validator::make($values, [
            'input_value' => ['required', 'integer', 'min:1'],
        ], [], [
            'input_value' => 'Jumlah Input'
        ]);

        if ($validator->fails()) {
            return response($this->validationMessages($validator), 400);
        }

        $inputMode = intval($values['input_mode']);

        $user = $request->user();
        $product = $request->product;
        $branch = $request->branch;
        $branchProducts = $branch->products;
        $branchProduct = $branchProducts->where('product_id', '=', $product->id)->first();

        $isNewProduct = empty($branchProduct);
        $branchStock = optional($branchProduct ? (($inputMode == STOCK_FLAG_ADMIN) ? $branchProduct->previousStock : $branchProduct->currentStock) : null);

        $realStock = optional($branchStock->real_stock);
        $isProductBox = ($product->satuan == PRODUCT_UNIT_BOX);

        $time = time();
        $thisWeek = $this->neo->dateRangeStockOpname($time, 'Y-m-d');

        $dataStock = [
            'id' => $branchStock->id,
            'date_from' => $thisWeek->start,
            'date_to' => $thisWeek->end,
            'stock_type' => $inputMode,
            'stock_info' => Arr::get(STOCK_FLAG_INFOS, $inputMode),
            'last_stock' => $branchStock->last_stock ?? 0,
            'output_stock' => $isProductBox ? ($realStock->boxOut ?? 0) : ($realStock->pcsOut ?? 0),
            'rest_stock' => $branchStock->rest_stock ?? 0,
            'input_manager' => $branchStock->input_manager ?? 0,
            'diff_stock' => $branchStock->diff_stock ?? 0,
            'input_admin' => $branchStock->input_admin ?? 0,
            'total_stock' => $isProductBox ? ($realStock->boxStock ?? 0) : ($realStock->pcsStock ?? 0),
            'total_balance' => $isProductBox ? ($realStock->boxBalance ?? 0) : ($realStock->pcsBalance ?? 0),
        ];

        $inputAdmin = intval($values['input_value']);

        $textMsg = 'disimpan';
        if (in_array($inputMode, [STOCK_FLAG_PLUS, STOCK_FLAG_MINUS])) {
            $lastInputAdmin = ($branchStock->input_admin > 0) ? $branchStock->input_admin : ($branchStock->input_manager ?? 0);

            if ($branchStock->stock_type == STOCK_FLAG_MINUS) {
                $lastInputAdmin = $branchStock->total_stock;
            }

            if ($inputMode == STOCK_FLAG_PLUS) {
                $textMsg = 'ditambahkan';
            } else {
                $inputAdmin = $inputAdmin * -1;
                $textMsg = 'dikurangi';
            }

            $inputAdmin = $lastInputAdmin + $inputAdmin;
        } else {
            if ($inputMode == STOCK_FLAG_ADMIN) {
                $dataStock['input_manager'] = 0;
                $dataStock['diff_stock'] = 0;
                $dataStock['last_stock'] = $dataStock['total_stock'];
                $dataStock['rest_stock'] = $dataStock['last_stock'] - $dataStock['output_stock'];
            }
        }

        $dataStock[($inputMode == STOCK_FLAG_EDIT) ? 'updated_by' : 'created_by'] = $user->id;
        $dataStock['total_stock'] = $dataStock['input_admin'] = $inputAdmin;

        $responCode = 200;
        $responText = route('main.branch.product.index');

        DB::beginTransaction();
        try {
            if ($isNewProduct) {
                $branchProduct = BranchProduct::create([
                    'product_id' => $product->id,
                    'branch_id' => $branch->id,
                    'is_active' => true,
                    'active_at' => $time,
                    'created_by' => $user->id,
                ]);
            }

            $dataStock['branch_product_id'] = $branchProduct->id;

            BranchStock::create($dataStock);

            DB::commit();

            session([
                'message' => "Persediaan produk berhasil {$textMsg}.",
                'messageClass' => 'success'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            $responCode = 500;
            $responText = view('partials.alert', [
                'message' => 'Telah terjadi kesalahan pada server. Silahkan coba lagi.',
                'messageClass' => 'danger'
            ])->render();
        }

        return response($responText, $responCode);
    }

    // download
    private function downloadQuery(Request $request)
    {
        $branchId = intval($request->get('branch_id', -1));
        $categories = ProductCategory::with(['products' => function ($p) {
            return $p->orderBy('name');
        }])
            ->whereHas('products')
            ->orderBy('name')->get();

        $branches = Branch::with([
            'productsFull',
            'distributors'
        ])->byActive();

        if ($branchId > 0) {
            $branches = $branches->byId($branchId);
        }

        return (object) [
            'categories' => $categories,
            'branchId' => $branchId,
            'branches' => $branches->orderBy('name')->get()
        ];
    }

    public function downloadExcel(Request $request)
    {
        $data = $this->downloadQuery($request);
        $branches = $data->branches;

        if ($branches->isEmpty()) {
            return response(new HtmlString('<h1 style="color:red">Tidak ada data yang dapat diunduh !!!</h1>'));
        }

        $branchId = $data->branchId;
        $categories = $data->categories;
        $time = strtotime(date('Y-m-d'));
        $currentWeek = $this->neo->dateRangeStockOpname($time);
        $previousWeek = $this->neo->dateRangeStockOpname(strtotime('-1 week', $time));

        $currentDateTitle = formatFullDate($currentWeek->start) . ' s/d ' . formatFullDate($currentWeek->end);
        $previousDateTitle = formatFullDate($previousWeek->start) . ' s/d ' . formatFullDate($previousWeek->end);

        $tglName = date('Ymd');
        $tglReport = [
            'current' => $currentDateTitle,
            'previous' => $previousDateTitle,
            'currentWeek' => $currentWeek,
            'previousWeek' => $previousWeek,
        ];

        $branchName = '';

        if ($branchId > 0) {
            $branch = $branches->first();
            $branchName = '-' . strtolower(str_replace(['-', ' ', '.', ','], ['_', '_', '_', '_'], $branch->name));
        }

        $downloadName = "Persediaan-Cabang{$branchName}-{$tglName}.xlsx";

        SimpleExcelWriter::streamDownload($downloadName, 'xlsx', function (Writer $writer) use ($downloadName, $tglReport, $branches, $categories) {

            $writer->openToBrowser($downloadName);

            $titleStyle = (new StyleBuilder)->setFontBold()->setFontSize(12)->setFontName('tahoma')->build();
            $headerStyle = (new StyleBuilder)->setFontBold()->setFontSize(10)->setFontName('tahoma')->setCellAlignment('center')->setBackgroundColor(Color::BLACK)->setFontColor(Color::WHITE)->build();
            $rowStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->build();
            $rowBoldStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->setFontBold()->build();
            $rowInActiveStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->setFontColor(Color::WHITE)->setBackgroundColor(Color::RED)->build();
            $rowCompletedStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->setFontColor(Color::WHITE)->setBackgroundColor(Color::GREEN)->build();
            $rowDifferenceStyle = (new StyleBuilder)->setFontSize(10)->setFontName('tahoma')->setBackgroundColor(Color::YELLOW)->build();

            $headerColumns = [
                new Cell('Produk'),
                new Cell('Input'),
                new Cell('Selisih'),
                new Cell('Admin'),
                new Cell('Stock'),
                new Cell('Output'),
                new Cell('Balance'),
            ];

            $sheetNo = 0;
            $sheetNames = [];

            foreach ($branches as $branch) {
                $sheet = ($sheetNo > 0) ? $writer->addNewSheetAndMakeItCurrent() : $writer->getCurrentSheet();
                $sheetNo++;

                $sheetName = $branch->name;
                $distributors = $branch->distributors;

                if (strlen($sheetName) > 20) {
                    $shNames = explode(' ', $sheetName);
                    $sheetName = implode('', array_map(function ($value) {
                        return strtoupper($value[0]);
                    }, array_values($shNames)));
                }

                $sheetRanges = Arr::get($sheetNames, $sheetName, []);
                $range = 0;
                if (!empty($sheetRanges)) {
                    $range = max($sheetRanges) + 1;
                }

                $sheetRanges[] = $range;
                $sheetNames[$sheetName] = $sheetRanges;

                if ($range > 0) {
                    $sheetName .= "-{$range}";
                }

                $sheet = $sheet->setName($sheetName);

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([new Cell('DAFTAR PERSEDIAAN BARANG')], $titleStyle));

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([
                    new Cell('Cabang'),
                    new Cell(':'),
                    new Cell($branch->name)
                ], $titleStyle));

                if ($distributors->isEmpty()) {
                    $writer->addRow(new Row([
                        new Cell('Distributor'),
                        new Cell(':'),
                        new Cell('-')
                    ], $titleStyle));
                } else {
                    $mgrRow = 0;
                    foreach ($distributors as $distributor) {
                        $writer->addRow(new Row([
                            new Cell(($mgrRow > 0) ? '' : 'Distributor'),
                            new Cell(($mgrRow > 0) ? '' : ':'),
                            new Cell($distributor->distributor_name)
                        ], $titleStyle));

                        $mgrRow++;
                    }
                }

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([
                    new Cell('Periode'),
                    new Cell(':'),
                    new Cell($tglReport['current'])
                ], $titleStyle));

                $writer->addRow(new Row($headerColumns, $headerStyle));

                $currentStart = $tglReport['currentWeek']->start;
                $branchProducts = $branch->stockProducts($currentStart, false);

                foreach ($categories as $category) {
                    $writer->addRow(new Row([
                        new Cell($category->name)
                    ], $rowBoldStyle));

                    foreach ($category->products as $product) {
                        $style = $rowStyle;
                        $isActiveProduct = $product->is_active;
                        if (!$isActiveProduct) $style = $rowInActiveStyle;

                        $branchProduct = $branchProducts->where('product_id', '=', $product->id)->first();
                        $hasProduct = !empty($branchProduct);
                        if ($isActiveProduct && !$hasProduct) $style = $rowInActiveStyle;

                        $branchProduct = optional($branchProduct);

                        $isProductBox = ($product->satuan == PRODUCT_UNIT_BOX);
                        $selectedStock = $branchProduct->selectedStock;
                        $hasStock = !empty($selectedStock);
                        $selectedStock = optional($selectedStock);
                        $realStock = optional($selectedStock->real_stock);
                        $propStock = $isProductBox ? 'boxStock' : 'pcsStock';
                        $propOutput = $isProductBox ? 'boxOut' : 'pcsOut';
                        $propBalance = $isProductBox ? 'boxBalance' : 'pcsBalance';

                        if ($isActiveProduct && $hasProduct) {
                            if (!$hasStock || ($realStock->$propStock == 0) || ($selectedStock->diff_stock != 0)) {
                                $style = $rowDifferenceStyle;
                            } else {
                                if ($realStock->$propBalance == 0) $style = $rowCompletedStyle;
                            }
                        }

                        $writer->addRow(new Row([
                            new Cell($product->name),
                            new Cell($selectedStock->input_manager ?? 0),
                            new Cell((($selectedStock->stock_type ?? STOCK_FLAG_MANAGER) != STOCK_FLAG_MANAGER) ? 0 : ($selectedStock->diff_stock ?? 0)),
                            new Cell($selectedStock->input_admin ?? 0),
                            new Cell($realStock->$propStock ?? 0),
                            new Cell($realStock->$propOutput ?? 0),
                            new Cell($realStock->$propBalance ?? 0),
                        ], $style));
                    }
                }

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([
                    new Cell('Periode'),
                    new Cell(':'),
                    new Cell($tglReport['previous'])
                ], $titleStyle));
                $writer->addRow(new Row($headerColumns, $headerStyle));

                $previousStart = $tglReport['previousWeek']->start;
                $branchProducts = $branch->stockProducts($previousStart, false);

                foreach ($categories as $category) {
                    $writer->addRow(new Row([
                        new Cell($category->name)
                    ], $rowBoldStyle));

                    foreach ($category->products as $product) {
                        $branchProduct = optional($branchProducts->where('product_id', '=', $product->id)->first());

                        $isProductBox = ($product->satuan == PRODUCT_UNIT_BOX);
                        $selectedStock = optional($branchProduct->selectedStock);
                        $realStock = optional($selectedStock->real_stock);
                        $propStock = $isProductBox ? 'boxStock' : 'pcsStock';
                        $propOutput = $isProductBox ? 'boxOut' : 'pcsOut';
                        $propBalance = $isProductBox ? 'boxBalance' : 'pcsBalance';

                        $writer->addRow(new Row([
                            new Cell($product->name),
                            new Cell($selectedStock->input_manager ?? 0),
                            new Cell((($selectedStock->stock_type ?? STOCK_FLAG_MANAGER) != STOCK_FLAG_MANAGER) ? 0 : ($selectedStock->diff_stock ?? 0)),
                            new Cell($selectedStock->input_admin ?? 0),
                            new Cell($realStock->$propStock ?? 0),
                            new Cell($realStock->$propOutput ?? 0),
                            new Cell($realStock->$propBalance ?? 0),
                        ], $rowStyle));
                    }
                }

                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([new Cell('')], null));
                $writer->addRow(new Row([new Cell('Keterangan')], $rowStyle));
                $writer->addRow(new Row([
                    new Cell('', $rowInActiveStyle),
                    new Cell('Produk sudah tidak aktif atau produk tidak disediakan untuk cabang ' . $branch->name, $rowStyle)
                ], null));
                $writer->addRow(new Row([
                    new Cell('', $rowDifferenceStyle),
                    new Cell('Distributor belum melakukan stock opname atau input masih ada selisih', $rowStyle)
                ], null));
                $writer->addRow(new Row([
                    new Cell('', $rowCompletedStyle),
                    new Cell('Produk habis terjual', $rowStyle)
                ], null));
            }
        })->toBrowser();
    }
}
