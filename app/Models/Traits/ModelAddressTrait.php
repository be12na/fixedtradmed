<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelAddressTrait
{
    // scope
    public function scopeLikeProvinceName(Builder $builder, string $provinceName): Builder
    {
        return $builder->where('province', 'like', "%{$provinceName}%");
    }

    public function scopeLikeCityName(Builder $builder, string $cityName): Builder
    {
        return $builder->where('city', 'like', "%{$cityName}%");
    }

    // accessor
    public function getCompleteAddressAttribute()
    {
        $addresses = [];
        if ($this->address) $addresses[] = $this->address;
        if ($this->village) $addresses[] = $this->village;
        if ($this->district) $addresses[] = 'Kec. ' . $this->district;
        if ($this->city) $addresses[] = $this->city;
        if ($this->province) $addresses[] = $this->province . '.';

        return implode(', ', $addresses) . ($this->pos_code ? ' Kode Pos ' . $this->pos_code : '');
    }
}
