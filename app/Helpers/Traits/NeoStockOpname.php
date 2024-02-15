<?php

namespace App\Helpers\Traits;

use App\Models\BranchStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

trait NeoStockOpname
{
    protected int $dayStockOpname = DAY_STOCKOPNAME_OPEN;
    protected bool $isOpenStockOpname = false;

    protected function initStockOpname(Carbon $today): void
    {
        $this->isOpenStockOpname = ($this->dayStockOpname == $today->dayOfWeek);
    }

    public function openStockOpname(): bool
    {
        return $this->isOpenStockOpname;
    }

    public function canStockOpname(): bool
    {
        $user = Auth::user();

        return ($user && $user->can_stock_opname && hasPermission('member.product.stock.index', $user) && $this->openStockOpname());
    }

    public function stockOpnameMessage()
    {
        $date = Carbon::today()->startOfWeek(DAY_STOCKOPNAME_START);
        $dayName = $date->dayName;

        return "Stock Opname hanya dapat dilakukan pada hari {$dayName}.";
    }

    public function dateRangeStockOpname($date = null, string $format = null): stdClass
    {
        if (is_null($date)) {
            $date = Carbon::today();
        } else {
            if (!($date instanceof Carbon)) {
                $date = Carbon::createFromTimestamp(!is_int($date) ? strtotime($date) : $date);
            }
        }

        $startWeek = (clone $date)->startOfWeek(DAY_STOCKOPNAME_START);
        $endWeek = (clone $startWeek)->endOfWeek(DAY_STOCKOPNAME_END);

        return (object) [
            'start' => is_null($format) ? $startWeek : $startWeek->translatedFormat($format),
            'end' => is_null($format) ? $endWeek : $endWeek->translatedFormat($format)
        ];
    }

    private function calculateStockOut(&$ret, bool $isBox, int $volume, bool $isOutPcs, int $outQty): void
    {
        $boxOut = 0;
        $pcsOut = 0;

        if ($isBox) {
            $boxOut = intval(ceil($outQty / ($isOutPcs ? $volume : 1)));
            $pcsOut = $outQty * ($isOutPcs ? 1 : $volume);
        } else {
            $pcsOut = $outQty;
        }

        $ret->boxOut += $boxOut;
        $ret->pcsOut += $pcsOut;
    }

    public function getProductStock(BranchStock $branchStock): stdClass
    {
        $result = (object) [
            'boxStock' => 0,
            'pcsStock' => 0,
            'boxOut' => 0,
            'pcsOut' => 0,
            'boxBalance' => 0,
            'pcsBalance' => 0,
        ];

        if (empty($branchStock) || !$branchStock->exists) return $result;

        $product = $branchStock->product->product;
        $branch = $branchStock->product->branch;

        $dateRange = $this->dateRangeStockOpname($branchStock->getRawOriginal('date_from'));
        // $startDate = $dateRange->start; // senin
        $startDate = $dateRange->start->subDay(); // minggu: karena di payment bisa kapan saja
        $endDate = $dateRange->end;

        $branchPayments = DB::table('branch_payments')
            ->join('branch_payment_details', function ($join) use ($product) {
                return $join->on('branch_payment_details.branch_payment_id', '=', 'branch_payments.id')
                    ->where('branch_payment_details.product_id', '=', $product->id)
                    ->whereNull('branch_payment_details.deleted_at');
            })
            ->select([
                'branch_payment_details.product_id',
                'branch_payment_details.product_unit',
                DB::raw('sum(branch_payment_details.product_qty) as qty')
            ])
            ->whereBetween('branch_payments.payment_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('branch_payments.branch_id', '=', $branch->id)
            ->whereNull('branch_payments.deleted_at')
            ->groupBy([
                'branch_payment_details.product_id',
                'branch_payment_details.product_unit',
            ])
            ->get();

        $branchSales = DB::table('branch_sales')
            ->join('branch_sales_products', function ($join) use ($product) {
                return $join->on('branch_sales_products.branch_sale_id', '=', 'branch_sales.id')
                    ->where('branch_sales_products.product_id', '=', $product->id)
                    ->where('branch_sales_products.is_active', '=', true)
                    ->whereNull('branch_sales_products.deleted_at');
            })
            ->select([
                'branch_sales_products.product_id',
                'branch_sales_products.product_unit',
                DB::raw('sum(branch_sales_products.product_qty) as qty')
            ])
            ->whereBetween('branch_sales.sale_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('branch_sales.branch_id', '=', $branch->id)
            ->where('branch_sales.is_active', '=', true)
            ->whereNull('branch_sales.deleted_at')
            ->groupBy([
                'branch_sales_products.product_id',
                'branch_sales_products.product_unit',
            ])
            ->get();

        $mitraPurchases = DB::table('mitra_purchases')
            ->join('mitra_purchase_products', function ($join) use ($product) {
                return $join->on('mitra_purchase_products.mitra_purchase_id', '=', 'mitra_purchases.id')
                    ->where('mitra_purchase_products.product_id', '=', $product->id)
                    ->where('mitra_purchase_products.is_active', '=', true)
                    ->whereNull('mitra_purchase_products.deleted_at');
            })
            ->select([
                'mitra_purchase_products.product_id',
                'mitra_purchase_products.product_unit',
                DB::raw('sum(mitra_purchase_products.product_qty) as qty')
            ])
            ->whereBetween('mitra_purchases.purchase_date', [$startDate->format('Y-m-d'), $endDate->addDay()->format('Y-m-d')])
            ->where('mitra_purchases.branch_id', '=', $branch->id)
            ->where('mitra_purchases.is_active', '=', true)
            ->where('mitra_purchases.is_transfer', '=', true)
            ->whereIn('mitra_purchases.purchase_status', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED])
            ->whereNull('mitra_purchases.deleted_at')
            ->groupBy([
                'mitra_purchase_products.product_id',
                'mitra_purchase_products.product_unit',
            ])
            ->get();

        $isProductBox = ($product->satuan == PRODUCT_UNIT_BOX);
        $productVolume = $isProductBox ? $product->isi : 1;

        $result->boxStock = $isProductBox ? $branchStock->total_stock : 0;
        $result->pcsStock = $branchStock->total_stock * $productVolume;

        foreach ($branchPayments as $branchPayment) {
            $this->calculateStockOut(
                $result,
                $isProductBox,
                $productVolume,
                ($branchPayment->product_unit == PRODUCT_UNIT_PCS),
                $branchPayment->qty
            );
        }

        foreach ($mitraPurchases as $mitraPurchase) {
            $this->calculateStockOut(
                $result,
                $isProductBox,
                $productVolume,
                ($mitraPurchase->product_unit == PRODUCT_UNIT_PCS),
                $mitraPurchase->qty
            );
        }

        foreach ($branchSales as $branchSale) {
            $this->calculateStockOut(
                $result,
                $isProductBox,
                $productVolume,
                ($branchSale->product_unit == PRODUCT_UNIT_PCS),
                $branchSale->qty
            );
        }

        $result->boxBalance = $result->boxStock - $result->boxOut;
        $result->pcsBalance = $result->pcsStock - $result->pcsOut;

        return $result;
    }
}
