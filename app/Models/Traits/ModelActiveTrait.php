<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelActiveTrait
{
    public function scopeByActive(Builder $builder, bool $active = null):Builder
    {
        return $builder->where('is_active', '=', is_null($active) ? true : $active);
    }
}
