<?php

namespace App\Models;

use App\Casts\FloatCast;
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
use Illuminate\Support\Arr;

class MitraPurchase extends Model
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
        'mitra_id',
        'mitra_type_id',
        'zone_id',
        'branch_id',
        'manager_id',
        'referral_id',
        'purchase_date',
        'savings',
        'is_active',
        'mitra_note',
        'admin_note',
        'is_delivery',
        'admin_confirmed',
        'admin_confirmed_at',
        'manager_confirmed',
        'manager_confirmed_at',
        'delivery_fee',
        'delivery_status',
        'delivery_status_at',
        'delivered_at',
        'received_at',
        'returned_at',
        'customer_identity',
        'customer_name',
        'customer_address',
        'customer_village_id',
        'customer_village',
        'customer_district_id',
        'customer_district',
        'customer_city_id',
        'customer_city',
        'customer_province_id',
        'customer_province',
        'customer_pos_code',
        'customer_phone',
        'is_transfer',
        'bank_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'total_purchase',
        'bonus_persen',
        'total_bonus',
        'discount_id',
        'discount_percent',
        'discount_amount',
        'total_zone_discount',
        'total_coupon_discount',
        'delivery_from',
        'delivery_to',
        'delivery_fee',
        'unique_digit',
        'total_transfer',
        'image_transfer',
        'transfer_at',
        'purchase_status',
        'status_at',
        'status_by',
        'status_note',
        'transfer_note',
        'delivery_code',
        'is_posted',
        'posted_at',
        'posted_by',
        'is_v2',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'code' => UppercaseCast::class,
        'purchase_date' => FullDateCast::class,
        'is_active' => 'boolean',
        'is_delivery' => 'boolean',
        'is_posted' => 'boolean',
        'savings' => 'integer',
        'is_mitra' => 'boolean',
        'is_deliver' => 'boolean',
        'delivery_fee' => 'integer',
        'admin_confirmed' => 'boolean',
        'admin_confirmed_at' => FullDatetimeCast::class,
        'manager_confirmed' => 'boolean',
        'manager_confirmed_at' => FullDatetimeCast::class,
        'delivery_status' => 'integer',
        'delivery_status_at' => FullDatetimeCast::class,
        'delivered_at' => FullDatetimeCast::class,
        'received_at' => FullDatetimeCast::class,
        'returned_at' => FullDatetimeCast::class,
        'posted_at' => FullDatetimeCast::class,
        'is_transfer' => 'boolean',
        'is_v2' => 'boolean',
        'total_purchase' => 'integer',
        'total_bonus' => 'integer',
        'bonus_persen' => FloatCast::class,
        'discount_percent' => FloatCast::class,
        'discount_amount' => 'integer',
        'unique_digit' => 'integer',
        'total_transfer' => 'integer',
        'transfer_at' => FullDatetimeCast::class,
        'status_at' => FullDatetimeCast::class,
        'purchase_status' => 'integer',
    ];

    // relation
    public function products()
    {
        return $this->hasMany(MitraPurchaseProduct::class, 'mitra_purchase_id', 'id')->with(['product']);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function mitra()
    {
        return $this->belongsTo(User::class, 'mitra_id', 'id');
    }

    public function omzetLogs()
    {
        return $this->hasMany(OmzetMember::class, 'transfer_id', 'id')->byFromMitra(true);
    }

    // public function transfer()
    // {
    //     return $this->hasOne(BranchTransferDetail::class, 'sale_id', 'id');
    // }

    public const IMAGE_DISK = 'transfer';
    public const IMAGE_ASSET = 'images/uploads/transfers/';

    // static
    public static function makeCode($saleDate)
    {
        if (!is_int($saleDate)) $saleDate = strtotime($saleDate);
        $date = date('Ymd', $saleDate);
        $randCode = str_pad((string) mt_rand(10001, 999999), 6, '0', STR_PAD_LEFT);

        return "PC{$date}{$randCode}";
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

    public function scopeByMitra(Builder $builder, User|int $mitra): Builder
    {
        $mitraId = ($mitra instanceof User) ? $mitra->id : $mitra;

        return $builder->where('mitra_id', '=', $mitraId);
    }

    public function scopeByDate(Builder $builder, $date): Builder
    {
        $purchaseDate = ($date instanceof Carbon) ? $date->format('Y-m-d') : $date;

        return $builder->where('purchase_date', '=', $purchaseDate);
    }

    public function scopeByBetweenDate(Builder $builder, $start, $end): Builder
    {
        $dateStart = ($start instanceof Carbon) ? $start->format('Y-m-d') : $start;
        $dateEnd = ($end instanceof Carbon) ? $end->format('Y-m-d') : $end;

        return $builder->whereBetween('purchase_date', [$dateStart, $dateEnd]);
    }

    public function scopeByTransfer(Builder $builder, bool $transfer = null): Builder
    {
        if (is_null($transfer)) return $builder;
        return $builder->where('is_transfer', '=', $transfer);
    }

    public function scopeByStatus(Builder $builder, array|int $status = null): Builder
    {
        if (is_null($status)) return $builder;
        if (is_array($status) && !empty($status)) {
            return $builder->whereIn('purchase_status', $status);
        }

        return $builder->where('purchase_status', '=', $status);
    }

    public function scopeByApproved(Builder $builder)
    {
        return $builder->byStatus(PROCESS_STATUS_APPROVED);
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

    public function getTotalPointAttribute()
    {
        return $this->products->sum('product_qty');
    }

    public function getHighestProductAttribute()
    {
        $detail = $this->products()->get()->sortByDesc('product.package_range')->first();
        $product = !empty($detail) ? $detail->product : null;
        $qty = !empty($detail) ? $detail->product_qty : 0;

        return (object) [
            'purchase_product' => $detail,
            'product' => $product,
            'qty' => $qty,
        ];
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

    public function getSumProfitMitraAttribute()
    {
        return $this->products->sum('profit_Mitra');
    }

    public function getSumFoundationAttribute()
    {
        return $this->products->sum('foundation');
    }

    public function getMitraNameAttribute()
    {
        return $this->mitra ? $this->mitra->name : null;
    }

    public function getManagerNameAttribute()
    {
        return $this->manager ? $this->manager->name : null;
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->name : null;
    }

    public function getStatusTextAttribute()
    {
        // $result = Arr::get(PROCESS_STATUS_LIST, $this->purchase_status);

        $result = 'Pending';

        if ($this->purchase_status == PROCESS_STATUS_PENDING) {
            $result = $this->is_transfer ? 'Menunggu Konfirmasi' : 'Transfer';
        } elseif (in_array($this->purchase_status, [PROCESS_STATUS_APPROVED, PROCESS_STATUS_REJECTED])) {
            $result = Arr::get(PROCESS_STATUS_LIST, $this->purchase_status);
        }

        return $result;
    }

    public function getImageUrlAttribute()
    {
        return $this->image_transfer ? asset(static::IMAGE_ASSET . $this->image_transfer) : '';
    }
}
