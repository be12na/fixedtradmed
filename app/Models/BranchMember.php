<?php

namespace App\Models;

use App\Casts\FullDatetimeCast;
use App\Casts\MediumDatetimeCast;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchMember extends Model
{
    use HasFactory;
    use ModelIDTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'branch_id',
        'position_ext',
        'manager_type',
        'is_active',
        'active_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'user_id' => 'integer',
        'branch_id' => 'integer',
        'is_active' => 'boolean',
        'active_at' => MediumDatetimeCast::class,
    ];

    // relation
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->byStatus(USER_STATUS_ACTIVE)->byBranchManager();
    }

    public function member()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->byStatus(USER_STATUS_ACTIVE);
    }

    // scope
    public function scopeByActive(Builder $builder): Builder
    {
        return $builder->where('is_active', '=', true);
    }

    public function scopeByManagers(Builder $builder): Builder
    {
        return $builder->whereIn('position_ext', [USER_EXT_DIST, USER_EXT_AG]);
    }

    public function scopeByDistributor(Builder $builder): Builder
    {
        return $builder->where('position_ext', '=', USER_EXT_DIST);
    }

    public function scopeByUser(Builder $builder, $user): Builder
    {
        $userId = is_object($user) ? $user->id : $user;
        return $builder->where('user_id', '=', $userId);
    }

    public function scopeByBranch(Builder $builder, $branch): Builder
    {
        $branchId = is_object($branch) ? $branch->id : $branch;
        return $builder->where('branch_id', '=', $branchId);
    }

    public function scopeByUserBranch(Builder $builder, $user, $branch): Builder
    {
        return $builder->byUser($user)->byBranch($branch);
    }

    // accessor
    public function getDistributorNameAttribute()
    {
        return $this->manager ? $this->manager->name : '-';
    }
}
