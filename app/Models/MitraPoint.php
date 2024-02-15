<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MitraPoint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'point_date',
        'user_id',
        'from_user_id',
        'point_type',
        'user_package_id',
        'purchase_id',
        'purchase_product_id',
        'product_id',
        'product_qty',
        'point_unit',
        'point',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'point_date' => 'date',
        'point_type' => 'integer',
        'product_qty' => 'integer',
        'point_unit' => 'integer',
        'point' => 'integer',
    ];

    // relationship
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id', 'id');
    }

    public function userPackage()
    {
        return $this->belongsTo(UserPackage::class, 'user_package_id', 'id');
    }

    public function purchase()
    {
        return $this->belongsTo(MitraPurchase::class, 'purchase_id', 'id');
    }

    public function purchaseProduct()
    {
        return $this->belongsTo(MitraPurchaseProduct::class, 'purchase_product_id', 'id');
    }
    // relationship:end

    // scope
    public function scopeByDates(Builder $builder, Carbon|string $startDate, Carbon|string $endDate): Builder
    {
        $start = ($startDate instanceof Carbon) ? $startDate->format('Y-m-d') : $startDate;
        $end = ($endDate instanceof Carbon) ? $endDate->format('Y-m-d') : $endDate;

        return $builder->whereBetween('point_date', [$start, $end]);
    }

    public function scopeByType(Builder $builder, int $type): Builder
    {
        return $builder->where('point_type', '=', $type);
    }

    public function scopeByUser(Builder $builder, User|int $user): Builder
    {
        return $builder->where('user_id', '=', is_int($user) ? $user : $user->id);
    }
    // scope:end

    // accessor
    public function getIsActivateMemberAttribute()
    {
        return ($this->point_type == POINT_TYPE_ACTIVATE_MEMBER);
    }
    // accessor:end
}
