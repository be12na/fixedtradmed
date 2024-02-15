<?php

namespace App\Models;

use App\Casts\FullDateCast;
use App\Casts\FullDatetimeCast;
use App\Casts\UppercaseCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchSale extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait, ModelCodeTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'branch_id',
        'manager_id',
        'manager_position',
        'manager_type',
        'salesman_id',
        'salesman_position',
        'sale_date',
        'savings',
        'is_active',
        'salesman_note',
        'is_posted',
        'posted_at',
        'posted_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'code' => UppercaseCast::class,
        'sale_date' => FullDateCast::class,
        'is_active' => 'boolean',
        'is_posted' => 'boolean',
        'savings' => 'integer',
        'posted_at' => FullDatetimeCast::class,
    ];

    // boot
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($branchSale) {
            foreach ($branchSale->products as $item) {
                $item->delete();
            }
        });
    }

    // relation
    public function products()
    {
        return $this->hasMany(BranchSalesProduct::class, 'branch_sale_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function salesman()
    {
        return $this->belongsTo(User::class, 'salesman_id', 'id');
    }

    public function transfer()
    {
        return $this->hasOne(BranchTransferDetail::class, 'sale_id', 'id')
            ->whereHas('branchTransfer', function ($branchTransfer) {
                return $branchTransfer->whereIn('transfer_status', [PROCESS_STATUS_PENDING, PROCESS_STATUS_APPROVED]);
            });
    }

    // static
    public static function makeCode($saleDate): string
    {
        if (!is_int($saleDate)) $saleDate = strtotime($saleDate);
        $date = date('Ymd', $saleDate);
        $randCode = str_pad((string) mt_rand(10001, 999999), 6, '0', STR_PAD_LEFT);

        return "BS{$date}{$randCode}";
    }

    public static function applyDeletedCode(self &$branchSale): void
    {
        $code = $branchSale->code;

        $lastData = static::withTrashed()->where('code', 'like', "{$code}-%")->orderBy('id', 'desc')->first();
        $lastRange = 0;
        if (!empty($lastData)) {
            $codes = explode('-', $lastData->code);
            if (count($codes) > 1) {
                $lastRange = intval($codes[1]);
            }
        }
        $lastRange += 1;
        $branchSale->code = "{$code}-{$lastRange}";
    }

    // scope
    public function scopeByPosted(Builder $builder, bool $posted = null): Builder
    {
        return $builder->where('is_posted', '=', is_null($posted) ? false : $posted);
    }

    public function scopeByBranch(Builder $builder, $branch): Builder
    {
        $branchId = is_object($branch) ? $branch->id : $branch;

        return $builder->where('branch_id', '=', $branchId);
    }

    public function scopeByManager(Builder $builder, $manager): Builder
    {
        $managerId = is_object($manager) ? $manager->id : $manager;

        return $builder->where('manager_id', '=', $managerId);
    }

    public function scopeInBranches(Builder $builder, array $branchIds): Builder
    {
        return $builder->whereIn('branch_id', $branchIds);
    }

    public function scopeBySalesman(Builder $builder, $salesmanId): Builder
    {
        return $builder->where('salesman_id', '=', $salesmanId);
    }

    public function scopeByDate(Builder $builder, $date): Builder
    {
        $saleDate = ($date instanceof Carbon) ? $date->format('Y-m-d') : $date;

        return $builder->where('sale_date', '=', $saleDate);
    }

    public function scopeByBetweenDate(Builder $builder, $start, $end): Builder
    {
        $dateStart = ($start instanceof Carbon) ? $start->format('Y-m-d') : $start;
        $dateEnd = ($end instanceof Carbon) ? $end->format('Y-m-d') : $end;

        return $builder->whereBetween('sale_date', [$dateStart, $dateEnd]);
    }

    // accessor
    public function getProductQuantityAttribute()
    {
        $groups = $this->products->groupBy('product_id');
        $result = $groups->map(function ($items, $key) {
            return (object) [
                'product_id' => $key,
                'quantity' => $items->sum('product_qty')
            ];
        });

        return $result;
    }

    public function getSumQuantityProductAttribute()
    {
        return $this->products->sum('product_qty');
    }

    public function getSumQuantityBoxAttribute()
    {
        return $this->products->sum('qty_box');
    }

    public function getSumQuantityPcsAttribute()
    {
        return $this->products->sum('qty_pcs');
    }

    public function getSumTotalPriceAttribute()
    {
        return $this->products->sum('total_price');
    }

    public function getSumTotalProfitAttribute()
    {
        return $this->products->sum('total_profit');
    }

    public function getSumProfitCrewAttribute()
    {
        return $this->products->sum('profit_crew');
    }

    public function getSumFoundationAttribute()
    {
        return $this->products->sum('foundation');
    }

    public function getSalesNameAttribute()
    {
        return $this->salesman ? $this->salesman->name : null;
    }

    public function getSalesPositionAttribute()
    {
        return app('appStructure')->getDataById(true, $this->salesman_position ?? -1);
    }

    public function getSalesPositionCodeAttribute()
    {
        return $this->sales_position ? $this->sales_position->code : $this->salesman->internal_position_code;
    }

    public function getSalesPositionNameAttribute()
    {
        return $this->sales_position ? $this->sales_position->name : $this->salesman->internal_position_name;
    }

    public function getManagerNameAttribute()
    {
        return $this->manager ? $this->manager->name : null;
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->name : null;
    }

    public function getMgrPositionAttribute()
    {
        return app('appStructure')->getDataById(false, $this->manager_position ?? -1);
    }

    public function getMgrPositionCodeAttribute()
    {
        return $this->mgr_position ? $this->mgr_position->code : null;
    }

    public function getMgrPositionNameAttribute()
    {
        return $this->mgr_position ? $this->mgr_position->name : null;
    }

    public function getIsTransferredAttribute()
    {
        return !empty($this->transfer);
    }
}
