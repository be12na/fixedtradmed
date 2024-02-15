<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelCodeTrait
{
    public function scopeByCode(Builder $builder, $code):Builder
    {
        return $builder->where('code', '=', $code);
    }
}
