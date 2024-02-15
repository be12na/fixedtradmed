<?php

namespace App\Models\Traits;

use App\Models\MitraPurchase;

trait UserShoppingTrait
{
    // relationship
    public function shoppings()
    {
        return $this->hasMany(MitraPurchase::class, 'mitra_id', 'id')->byActive();
    }

    public function completeShoppings()
    {
        return $this->hasMany(MitraPurchase::class, 'mitra_id', 'id')
            ->byActive()
            ->byApproved()
            ->with('products', function ($with) {
                return $with->with('product');
            });
    }
    // relationship:end

    // accessor
    public function getHasPackageAttribute()
    {
        return ($this->completeShoppings()->count() > 0);
    }

    public function getHasRepeatOrderAttribute()
    {
        return ($this->completeShoppings()->count() > 1);
    }

    public function getActivePackageAttribute()
    {
        if (!$this->has_package) return null;

        $shoppings = $this->completeShoppings;
        $highest = $shoppings->sortByDesc('products.product.package_range')->first();

        return $highest->products->first()->product;
    }

    public function getFirstShoppingAttribute()
    {
        if (!$this->has_package) return null;

        return $this->completeShoppings->sortBy('id')->first();
    }

    public function getTotalQtyShoppingAttribute()
    {
        return $this->completeShoppings->sum(function ($item) {
            return $item->sum('products.product_qty');
        });
    }

    public function getFirstPointSponsorAttribute()
    {
        if (empty($first = $this->first_shopping)) return 0;

        return $first->total_point;
    }

    public function getTotalPointShoppingAttribute()
    {
        return $this->completeShoppings->sum(function ($item) {
            return $item->total_point;
        }) - $this->first_point_sponsor;
    }

    public function getTotalPointSponsoringAttribute()
    {
        $firstSponsoringPoint = $this->myMembers->sum(function ($item) {
            return $item->first_point_sponsor;
        });

        return $firstSponsoringPoint;
    }

    public function getTotalPointRoAttribute()
    {
        return $this->total_point_sponsoring + $this->total_point_shopping;
    }
    // accessor:end
}
