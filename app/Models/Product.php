<?php

namespace App\Models;

// use App\Casts\FloatCast;
use App\Casts\HtmlStringCast;
use App\Casts\UppercaseCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Product extends Model
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
        'product_category_id',
        'name',
        'satuan',
        'isi',
        'satuan_isi',
        'notes',
        'description',
        'harga_a', // harga satuan jual
        'harga_b', // harga jual dropshipper
        'harga_c', // harga jual reseller
        'harga_d', // harga jual distributor
        // 'eceran_a',
        'eceran_a', // belum digunakan
        'eceran_b', // blum digunakan
        'eceran_c', // blum digunakan
        'eceran_d', // harga pokok penjualan (HPP)
        'image',
        'is_active',
        'active_at',
        'is_publish',
        'self_point',
        'upline_point',
        'bonus_sponsor',
        'bonus_sponsor_ro',
        'bonus_cashback',
        'bonus_cashback_condition',
        'bonus_cashback_ro',
        'bonus_cashback_ro_condition',
        'package_range',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'code' => UppercaseCast::class,
        'satuan' => 'integer',
        'isi' => 'integer',
        'active_at' => 'datetime',
        'satuan_isi' => 'integer',
        'harga_a' => 'integer',
        'harga_b' => 'integer',
        'harga_c' => 'integer',
        'harga_d' => 'integer',
        'eceran_a' => 'integer',
        'eceran_b' => 'integer',
        'eceran_c' => 'integer',
        'eceran_d' => 'integer',
        'self_point' => 'integer',
        'upline_point' => 'integer',
        'is_active' => 'boolean',
        'is_publish' => 'boolean',
        'notes' => HtmlStringCast::class,
        'description' => HtmlStringCast::class,
        'bonus_sponsor' => 'integer',
        'bonus_sponsor_ro' => 'integer',
        'bonus_cashback_ro' => 'integer',
        'package_range' => 'integer',
    ];

    public const IMAGE_DISK = 'product';
    public const IMAGE_ASSET = 'images/uploads/products/';

    // relation
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    // public function productBranch()
    // {
    //     return $this->hasMany(BranchProduct::class, 'product_id', 'id');
    // }

    // public function prices()
    // {
    //     return $this->hasMany(ProductPrice::class, 'product_id', 'id')->whereHas('zone');
    // }

    // public function mitraDiscount()
    // {
    //     return $this->hasMany(ProductDiscount::class, 'product_id', 'id')->byMitraType(MITRA_TYPE_AGENT);
    // }

    // public function mitraReward()
    // {
    //     return $this->hasMany(ProductReward::class, 'product_id', 'id');
    // }

    // scope
    // public function scopeAvailableProductBranch(Builder $builder, Branch $branch, $currentProductId = null): Builder
    // {
    //     $branchId = $branch ? $branch->id : 0;

    //     return $builder->where(function (Builder $b) use ($branchId, $currentProductId) {
    //         $b->whereDoesntHave('productBranch', function (Builder $x) use ($branchId) {
    //             $x->where('branch_id', '=', $branchId);
    //         });
    //         if (!is_null($currentProductId)) $b->orWhere('id', '=', $currentProductId);
    //     });
    // }

    public function scopeByCategory(Builder $builder, $category): Builder
    {
        $categoryId = is_object($category) ? $category->id : ($category ?? -1);
        return ($categoryId <= 0) ? $builder : $builder->where('product_category_id', '=', $categoryId);
    }

    public function scopeByPublished(Builder $builder, bool $publish = null): Builder
    {
        return $builder->where('is_publish', '=', is_null($publish) ? true : $publish);
    }

    public function scopeByHasPrice(Builder $builder, string $field = null): Builder
    {
        $field = $field ?? 'harga_a';

        return $builder->where($field, '>', 0);
    }

    public function scopeByPackageRange(Builder $builder, int $range): Builder
    {
        return $builder->where('package_range', '=', $range);
    }

    // static
    public static function lastPackageRange()
    {
        return static::query()->max('package_range');
    }
    // static:end

    // accessor
    public function getImageUrlAttribute()
    {
        return asset(static::IMAGE_ASSET . $this->image);
    }

    public function getCategoryNameAttribute()
    {
        return $this->category ? $this->category->name : null;
    }

    // public function getKomisiAttribute()
    // {
    //     $setting = app('neo')->settingBonusSell();

    //     return optional($setting)->percent ?? 0;
    // }

    public function getProductUnitAttribute()
    {
        return strtoupper(Arr::get(PRODUCT_UNITS, $this->satuan, ''));
    }

    public function getVolumeProductUnitAttribute()
    {
        if ($this->satuan == PRODUCT_UNIT_BOX) {
            return strtoupper(Arr::get(PRODUCT_UNITS, $this->satuan_isi, ''));
        }

        return null;
    }

    // public function getPackageNameAttribute()
    // {
    //     return $this->category->name . ' ' . $this->package_range;
    // }

    // tradmed
    public function getSellPriceAttribute()
    {
        return $this->harga_a ?? 0;
    }

    public function getDropshipperPriceAttribute()
    {
        return $this->harga_b ?? 0;
    }

    public function getResellerPriceAttribute()
    {
        return $this->harga_c ?? 0;
    }

    public function getDistributorPriceAttribute()
    {
        return $this->harga_d ?? 0;
    }

    public function getHppAttribute()
    {
        return $this->eceran_d ?? 0;
    }
    // tradmed:end
    // accessor:end

    // public method
    public function actualPrice(int $packageId): int
    {
        $result = $this->harga_a;

        if (($packageId == PRODUCT_DISTRIBUTOR) && ($this->harga_d > 0)) {
            $result = $this->harga_d;
        } elseif (($packageId == PRODUCT_RESELLER) && ($this->harga_c > 0)) {
            $result = $this->harga_c;
        } elseif (($packageId == PRODUCT_DISTRIBUTOR) && ($this->harga_b > 0)) {
            $result = $this->harga_b;
        }

        return $result;
    }

    public function zonePrice(int $zone)
    {
        if ($zone == ZONE_WEST) return $this->harga_a;
        if ($zone == ZONE_EAST) return $this->harga_b;

        return 0;
    }

    public function eceranZonePrice(int $zone)
    {
        if ($zone == ZONE_WEST) return ($this->satuan == PRODUCT_UNIT_BOX) ? $this->eceran_a : 0;
        if ($zone == ZONE_EAST) return ($this->satuan == PRODUCT_UNIT_BOX) ? $this->eceran_b : 0;

        return 0;
    }

    public function zoneMitraPrice(int $zone)
    {
        if ($zone == ZONE_WEST) return $this->harga_a;
        if ($zone == ZONE_EAST) return $this->harga_b;

        return 0;
    }

    public function zoneMitraPriceList(): array
    {
        $list = [$this->zoneMitraPrice(ZONE_WEST), $this->zoneMitraPrice(ZONE_EAST)];

        $min = min($list);
        $max = max($list);

        return ($min != $max) ? [$min, $max] : [$min];
    }

    public function zonePriceV2(int $zone)
    {
        $price = optional($this->prices->where('zone_id', '=', $zone)->first());
        return $price->normal_price ?? 0;
    }

    public function eceranZonePriceV2(int $zone)
    {
        $price = optional($this->prices->where('zone_id', '=', $zone)->first());

        return ($this->satuan == PRODUCT_UNIT_BOX) ? ($price->normal_retail_price ?? 0) : 0;
    }

    public function zoneMitraPriceV2(int $zone, int $qty = null)
    {
        $price = optional($this->prices->where('zone_id', '=', $zone)->first());
        $result = $price->mitra_price ?? 0;
        if (($result > 0) && !is_null($qty) && ($qty > 0)) {
            $productDiscount = $this->mitraDiscount->where('min_qty', '<=', $qty)->sortBy(['min_qty', 'desc'])->first();
            if (!empty($productDiscount)) {
                $result = $result - $productDiscount->discount;
            }
        }

        return $result;
    }

    public function zoneMitraPromoPriceV2(int $zone, int $qty = null)
    {
        $price = optional($this->prices->where('zone_id', '=', $zone)->first());
        $result = $price->mitra_promo_price ?? 0;
        if (($result > 0) && !is_null($qty) && ($qty > 0)) {
            $productDiscount = $this->mitraDiscount->where('min_qty', '<=', $qty)->sortBy(['min_qty', 'desc'])->first();
            if (!empty($productDiscount)) {
                $result = $result - $productDiscount->discount;
            }
        }

        return $result;
    }

    public function zoneMitraPriceV2List(): array
    {
        if ($this->prices->isEmpty()) return [0];

        $min = $this->prices->min('mitra_price');
        $max = $this->prices->max('mitra_price');

        return ($min != $max) ? [$min, $max] : [$min];
    }
}
