<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ModelIDTrait
{
    public function scopeById(Builder $builder, $id): Builder
    {
        return is_array($id) ? $builder->whereIn('id', $id) : $builder->where('id', '=', $id);
    }
}
