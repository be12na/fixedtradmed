<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraRewardClaim extends Model
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
        'reward_id',
        'status',
        'status_at',
        'status_note',
        'status_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'integer',
        'status_at' => 'datetime',
    ];

    // relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reward()
    {
        return $this->belongsTo(MitraReward::class, 'reward_id', 'id');
    }
    // relationship:end

    // scope
    public function scopeByUser(Builder $builder, User|int $user): Builder
    {
        return $builder->where('user_id', '=', ($user instanceof User) ? $user->id : $user);
    }

    public function scopeOnStatus(Builder $builder): Builder
    {
        return $builder->whereIn('status', [CLAIM_STATUS_PENDING, CLAIM_STATUS_FINISH]);
    }

    public function scopeOnPending(Builder $builder): Builder
    {
        return $builder->whereIn('status', [CLAIM_STATUS_PENDING]);
    }

    public function scopeOnFinish(Builder $builder): Builder
    {
        return $builder->where('status', '=', CLAIM_STATUS_FINISH);
    }
    // scope:end

    // accessor
    public function getIsPendingAttribute()
    {
        return ($this->status == CLAIM_STATUS_PENDING);
    }

    public function getIsFinishAttribute()
    {
        return ($this->status == CLAIM_STATUS_FINISH);
    }

    public function getIsCancelAttribute()
    {
        return ($this->status == CLAIM_STATUS_CANCEL);
    }

    public function getClaimDateAttribute()
    {
        return formatFullDate($this->created_at);
    }
    // accessor:end
}
