<?php

namespace App\Models;

use App\Casts\UppercaseCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Branch extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelCodeTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'wilayah',
        'address',
        'pos_code',
        'telp',
        'is_active',
        'is_stock',
        'zone_id',
        'active_at',
        'active_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'code' => UppercaseCast::class,
        'wilayah' => 'integer',
        'is_active' => 'boolean',
        'is_stock' => 'boolean',
    ];

    // relation
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id')->byActive();
    }

    public function manager()
    {
        return $this->hasMany(BranchMember::class, 'branch_id', 'id')->byActive()->whereHas('manager');
    }

    public function managers()
    {
        return $this->hasMany(BranchMember::class, 'branch_id', 'id')
            ->byActive()
            ->byManagers()
            ->whereHas('manager');
    }

    public function distributors()
    {
        return $this->hasMany(BranchMember::class, 'branch_id', 'id')
            ->byActive()
            ->byDistributor()
            ->whereHas('manager')
            ->with(['manager']);
    }

    public function members()
    {
        return $this->hasMany(BranchMember::class, 'branch_id', 'id')
            ->byActive()
            ->whereHas('member');
    }

    public function products()
    {
        return $this->hasMany(BranchProduct::class, 'branch_id', 'id')->byActive();
    }

    public function productsFull()
    {
        return $this->hasMany(BranchProduct::class, 'branch_id', 'id');
        // ->with([
        //     'product.category',
        //     'beforePreviousStock',
        //     'previousStock',
        //     'currentStock',
        //     'lastStock',
        // ]);
    }

    public function branchTransfers()
    {
        return $this->hasMany(BranchTransfer::class, 'branch_id', 'id')->whereNull('deleted_at');
    }

    public function sales()
    {
        return $this->hasMany(BranchSale::class, 'branch_id', 'id')->byActive()->whereNull('deleted_at');
    }

    public function mitraOrders()
    {
        return $this->hasMany(MitraPurchase::class, 'branch_id', 'id')->byActive()->whereNull('deleted_at');
    }

    public function mitraDealOrders()
    {
        return $this->hasMany(MitraPurchase::class, 'branch_id', 'id')
            ->byActive()
            ->whereNull('deleted_at')
            ->byTransfer(true)
            ->byStatus(PROCESS_STATUS_APPROVED);
    }

    public function mitraTransferredOrders()
    {
        return $this->hasMany(MitraPurchase::class, 'branch_id', 'id')
            ->byActive()
            ->whereNull('deleted_at')
            ->byTransfer(true)
            ->byStatus([PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
    }

    public function currentWeekSales()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname($time, 'Y-m-d');
        return $this->sales()->byBetweenDate($dates->start, $dates->end);
    }

    public function previousWeekSales()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-1 week', $time), 'Y-m-d');
        return $this->sales()->byBetweenDate($dates->start, $dates->end);
    }

    public function beforePreviousWeekSales()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-2 week', $time), 'Y-m-d');
        return $this->sales()->byBetweenDate($dates->start, $dates->end);
    }

    public function currentWeekMitra()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname($time, 'Y-m-d');
        return $this->mitraTransferredOrders()->byBetweenDate($dates->start, $dates->end);
    }

    public function previousWeekMitra()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-1 week', $time), 'Y-m-d');
        return $this->mitraTransferredOrders()->byBetweenDate($dates->start, $dates->end);
    }

    public function beforePreviousWeekMitra()
    {
        $time = strtotime(date('Y-m-d'));
        $dates = app('neo')->dateRangeStockOpname(strtotime('-2 week', $time), 'Y-m-d');
        return $this->mitraTransferredOrders()->byBetweenDate($dates->start, $dates->end);
    }

    // scope
    public function scopeByCanStock(Builder $builder, bool $isStock = null): Builder
    {
        return $builder->where('is_stock', '=', is_null($isStock) ? false : $isStock);
    }

    // accessor
    public function getHasZoneAttribute()
    {
        return !empty($this->zone);
    }

    public function getZoneNameAttribute()
    {
        // if (!isAppV2()) return Arr::get(BRANCH_ZONES, $this->wilayah ?? 0);

        // return optional($this->zone)->name;

        return Arr::get(BRANCH_ZONES, $this->wilayah ?? 0);
    }

    public function getZoneNameV2Attribute()
    {
        return optional($this->zone)->name;
    }

    public function getFullZoneNameV2Attribute()
    {
        return optional($this->zone)->full_name;
    }

    public function getStockProductsAttribute()
    {
        $products = $this->productsFull->pluck('product');
        $productIds = $products->pluck('id')->toArray();
        $categoryIds = $products->pluck('product_category_id')->toArray();

        $categories = ProductCategory::whereIn('id', $categoryIds)
            ->whereHas('products', function ($qProduct) use ($productIds) {
                return $qProduct->whereIn('id', $productIds);
            })
            ->with(['products' => function ($qProduct) use ($productIds) {
                return $qProduct->whereIn('id', $productIds)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $summariesStock = collect();

        $currentOutputs = $this->summaryPeriodicProductOut($this->currentWeekSales);
        $previousOutputs = $this->summaryPeriodicProductOut($this->previousWeekSales);
        $beforePreviousOutputs = $this->summaryPeriodicProductOut($this->beforePreviousWeekSales);

        $currentMitraOutputs = $this->summaryPeriodicProductOut($this->currentWeekMitra);
        foreach ($currentMitraOutputs as $mitraOutput) {
            if ($productOut = $currentOutputs->where('product_id', '=', $mitraOutput->product_id)->first()) {
                $productOut->quantity += $mitraOutput->quantity;
            } else {
                $currentOutputs->push($mitraOutput);
            }
        }

        $previousMitraOutputs = $this->summaryPeriodicProductOut($this->previousWeekMitra);
        foreach ($previousMitraOutputs as $mitraOutput) {
            if ($productOut = $previousOutputs->where('product_id', '=', $mitraOutput->product_id)->first()) {
                $productOut->quantity += $mitraOutput->quantity;
            } else {
                $previousOutputs->push($mitraOutput);
            }
        }

        $beforePreviousMitraOutputs = $this->summaryPeriodicProductOut($this->beforePreviousWeekMitra);
        foreach ($beforePreviousMitraOutputs as $mitraOutput) {
            if ($productOut = $beforePreviousOutputs->where('product_id', '=', $mitraOutput->product_id)->first()) {
                $productOut->quantity += $mitraOutput->quantity;
            } else {
                $beforePreviousOutputs->push($mitraOutput);
            }
        }

        foreach ($this->productsFull as $branchProduct) {
            $productId = $branchProduct->product_id;
            $currentOutput = $currentOutputs->where('product_id', '=', $productId)->sum('quantity');
            $previousOutput = $previousOutputs->where('product_id', '=', $productId)->sum('quantity');
            $beforePreviousOutput = $beforePreviousOutputs->where('product_id', '=', $productId)->sum('quantity');

            $currentStock = optional($branchProduct->currentStock);
            $previousStock = optional($branchProduct->previousStock);
            $beforePreviousStock = optional($branchProduct->beforePreviousStock);

            $previousSummary = (object) [
                'inputManager' => 0,
                'diff' => 0,
                'inputAdmin' => 0,
                'stock' => 0,
                'output' => 0,
                'balance' => 0,
            ];
            $currentSummary = clone $previousSummary;

            // minggu lalu
            $previousSummary->id = $previousStock->id;
            $previousSummary->branch_product_id = $previousStock->branch_product_id;
            $previousSummary->inputManager = $previousStock->input_manager ?? 0;
            $previousSummary->inputAdmin = $previousStock->input_admin ?? 0;

            $beforePreviousBalance = ($beforePreviousStock->total_stock ?? 0) - $beforePreviousOutput;
            if (in_array($previousStock->stock_type ?? -1, [STOCK_FLAG_MANAGER, STOCK_FLAG_EDIT])) {
                if ($beforePreviousBalance > 0) {
                    $previousSummary->diff = ($previousStock->input_manager ?? 0) - $beforePreviousBalance;
                }
            }

            $previousSummary->stock = $previousStock->total_stock ?? 0;
            $previousSummary->output = $previousOutput;
            $previousSummary->balance = $previousSummary->stock - $previousOutput;

            // minggu ini
            $currentSummary->id = $currentStock->id;
            $currentSummary->branch_product_id = $currentStock->branch_product_id;
            $currentSummary->inputManager = $currentStock->input_manager ?? 0;
            $currentSummary->inputAdmin = $currentStock->input_admin ?? 0;

            if (in_array($currentStock->stock_type ?? -1, [STOCK_FLAG_MANAGER, STOCK_FLAG_EDIT])) {
                if ($previousSummary->balance > 0) {
                    $currentSummary->diff = ($currentStock->input_manager ?? 0) - $previousSummary->balance;
                }
            }

            $currentSummary->stock = $currentStock->total_stock ?? 0;
            $currentSummary->output = $currentOutput;
            $currentSummary->balance = $currentSummary->stock - $currentOutput;

            $product = $products->where('id', '=', $productId)->first();

            $object = (object) [
                'category_id' => $product->product_category_id,
                'category_name' => $product->category_name,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product' => $product,
                'summary' => (object) [
                    'previous' => $previousSummary,
                    'current' => $currentSummary
                ]
            ];

            $summariesStock->push($object);
        }

        return (object) [
            'categories' => $categories,
            'summariesStock' => $summariesStock,
        ];
    }

    private function summaryPeriodicProductOut(EloquentCollection $transactions): Collection
    {
        $productsSummary = collect();
        foreach ($transactions as $transaction) {
            foreach ($transaction->product_quantity as $qty) {
                $productsSummary->push($qty);
            }
        }

        $productIds = array_unique($productsSummary->pluck('product_id')->toArray());

        $result = collect();

        foreach ($productIds as $productId) {
            $result->push((object) [
                'product_id' => $productId,
                'quantity' => $productsSummary->where('product_id', '=', $productId)->sum('quantity'),
            ]);
        }

        return $result;
    }

    // public
    public function stockProducts(Carbon|int|string $time, bool $activeOnly)
    {
        $carbonDate = $time;
        if (!($time instanceof Carbon)) {
            $intTime = is_int($time) ? $time : strtotime($time);
            $carbonDate = Carbon::createFromTimestamp($intTime);
        }

        $dateStock = app('neo')->dateRangeStockOpname($carbonDate, 'Y-m-d');

        $branchProduct = BranchProduct::byBranch($this->id);

        if ($activeOnly) {
            $branchProduct = $branchProduct->byActive();
        }

        return $branchProduct
            ->with(['selectedStock' => function ($branchStock) use ($dateStock) {
                return $branchStock->byDates($dateStock->start, $dateStock->end)
                    ->with('product');
            }])
            ->get();
    }
}
