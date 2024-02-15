<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait UserPositionTrait
{
    // scope
    public function scopeByInternalPosition(Builder $builder, $position, string $operator = null): Builder
    {
        if (empty($operator)) $operator = '=';
        return $builder->where('position_int', $operator, $position);
    }

    public function scopeByBranchManager(Builder $builder): Builder
    {
        return $builder->byInternalPosition(USER_INT_MGR);
    }

    // accessor
    public function getDataInternalPositionAttribute()
    {
        return app('appStructure')->getDataById(true, $this->position_int ?? 0);
    }

    public function getInternalPositionCodeAttribute()
    {
        return $this->data_internal_position ? $this->data_internal_position->code : null;
    }

    public function getInternalPositionNameAttribute()
    {
        return $this->data_internal_position ? $this->data_internal_position->name : null;
    }
}
