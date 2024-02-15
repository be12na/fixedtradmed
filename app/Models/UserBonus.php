<?php

namespace App\Models;

use App\Models\Traits\ModelIDTrait;
use App\Notifications\BonusNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class UserBonus extends Model
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
        'from_user_id',
        'bonus_type',
        'level',
        'bonus_date',
        'bonus_amount',
        'should_upgrade',
        'should_ro',
        'ro_id',
        'wd_id',
        'purchase_id',
        'purchase_product_id',
        'product_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bonus_type' => 'integer',
        'level' => 'integer',
        'bonus_date' => 'date',
        'bonus_amount' => 'integer',
        'created_at' => 'datetime',
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

    public function withdraw()
    {
        return $this->belongsTo(UserWithdraw::class, 'wd_id', 'id')
            ->byTransStatus([CLAIM_STATUS_PENDING, CLAIM_STATUS_FINISH]);
    }

    public function purchase()
    {
        return $this->belongsTo(MitraPurchase::class, 'purchase_id', 'id');
    }

    public function purchaseProduct()
    {
        return $this->belongsTo(MitraPurchaseProduct::class, 'purchase_product_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    // relationship:end

    // scope
    public function scopeByUser(Builder $builder, User|int $user): Builder
    {
        return $builder->where('user_id', '=', ($user instanceof User) ? $user->id : $user);
    }

    public function scopeByType(Builder $builder, array|int $type): Builder
    {
        if (!is_array($type)) $type = [$type];

        return $builder->whereIn('bonus_type', $type);
    }

    public function scopeByDates(Builder $builder, Carbon|string $startDate, Carbon|string $endDate): Builder
    {
        $start = ($startDate instanceof Carbon) ? $startDate->format('Y-m-d') : $startDate;
        $end = ($endDate instanceof Carbon) ? $endDate->format('Y-m-d') : $endDate;

        return $builder->whereBetween('bonus_date', [$start, $end]);
    }

    public function scopeForWithdraw(Builder $builder, Carbon $date = null, $operator = '<', Closure $userConditions = null): Builder
    {
        if (is_null($date)) $date = Carbon::today();

        return $builder
            ->where('bonus_date', $operator, $date->format('Y-m-d'))
            ->whereDoesntHave('withdraw')
            ->whereHas('user', function ($user) use ($userConditions) {
                if (!is_null($userConditions)) $user = $userConditions($user);

                return $user->whereHas('memberActiveBank')
                    ->whereHas('completeShoppings');
            });
    }
    // scope:end

    // accessor
    public function getBonusTypeNameAttribute()
    {
        return Arr::get(BONUS_MITRA_NAMES, $this->bonus_type, '-');
    }
    // accessor:end

    // static
    public static function createBonusSponsor(MitraPurchase $purchase, Carbon $date = null): void
    {
        $user = $purchase->mitra;
        $sponsor = $user->referral;

        if (!empty($sponsor)) {
            foreach ($purchase->products as $detail) {
                $product = $detail->product;
                $bonus = $product->bonus_sponsor * $detail->product_qty;

                if ($bonus > 0) {
                    $result = static::create([
                        'user_id' => $sponsor->id,
                        'from_user_id' => $user->id,
                        'bonus_type' => BONUS_MITRA_SPONSOR,
                        'bonus_date' => carbonToday($date),
                        'bonus_amount' => $bonus,
                        'should_upgrade' => !$sponsor->has_package,
                        'purchase_product_id' => $detail->id,
                        'product_id' => $product->id,
                    ]);

                    $sponsor->notify(new BonusNotification($sponsor, 'database', ['driver' => 'mail', 'id' => $result->id]));
                    $sponsor->notify(new BonusNotification($sponsor, 'database', ['driver' => 'onesender', 'id' => $result->id]));
                }
            }
        }
    }

    public static function createBonusCashbackRO(MitraPurchase $purchase, Carbon $date = null): void
    {
        $user = $purchase->mitra;

        foreach ($purchase->products as $detail) {
            $product = $detail->product;
            $bonus = $product->bonus_cashback * $detail->product_qty;

            if ($bonus > 0) {
                $result = static::create([
                    'user_id' => $user->id,
                    'bonus_type' => BONUS_MITRA_CASHBACK_RO,
                    'bonus_date' => carbonToday($date),
                    'bonus_amount' => $bonus,
                    'should_ro' => false,
                    'purchase_product_id' => $detail->id,
                    'product_id' => $product->id,
                ]);

                $user->notify(new BonusNotification($user, 'database', ['driver' => 'mail', 'id' => $result->id]));
                $user->notify(new BonusNotification($user, 'database', ['driver' => 'onesender', 'id' => $result->id]));
            }
        }
    }

    public static function createBonusPointRO(MitraPurchase $purchase, bool $forSelf, Carbon $date = null): void
    {
        $mitra = $purchase->mitra;
        $user = $forSelf ? $mitra : $mitra->referral;

        if (
            !empty($user)
            && $user->has_package
            && !empty($product = $user->active_package)
            && (($condition = $product->bonus_cashback_ro_condition) > 0)
            && (($bonus = $product->bonus_cashback_ro) > 0)
        ) {
            $totalPointRO = $user->total_point_ro + $purchase->total_point;
            $countBonus = static::query()->byUser($user)->byType(BONUS_MITRA_POINT_RO)->count();
            $bonusCondition = $countBonus * $condition;
            $conditionNow = $totalPointRO - $bonusCondition;

            if ($conditionNow < $condition) {
                $bonus = 0;
            }

            if ($bonus > 0) {
                $result = static::create([
                    'user_id' => $user->id,
                    'bonus_type' => BONUS_MITRA_POINT_RO,
                    'bonus_date' => carbonToday($date),
                    'bonus_amount' => $bonus,
                    'bonus_date' => carbonToday($date),
                    'bonus_amount' => $bonus,
                    'purchase_id' => $purchase->id,
                ]);

                $user->notify(new BonusNotification($user, 'database', ['driver' => 'mail', 'id' => $result->id]));
                $user->notify(new BonusNotification($user, 'database', ['driver' => 'onesender', 'id' => $result->id]));
            }
        }
    }

    public static function createBonusLevel(MitraPurchase $purchase, int $type, Carbon $date = null): void
    {
        $fromUser = $purchase->mitra;
        $isPrestasi = ($type == BONUS_MITRA_LEVEL_PRESTASI);
        $bonusType = $isPrestasi ? BONUS_MITRA_PRESTASI : BONUS_MITRA_GENERASI;
        // $totalCountProduct = $purchase->total_point;
        $totalPriceProduct = $purchase->sum_total_price;
        $level = 1;
        $user = $fromUser;

        while (!empty($upline = $user->referral)) {
            $bonus = MitraBonusLevel::bonus_amount($type, $level) / 100;
            $bonusAmount = floor($bonus * $totalPriceProduct);

            if ($bonusAmount > 0) {
                $result = static::create([
                    'user_id' => $upline->id,
                    'from_user_id' => $fromUser->id,
                    'bonus_type' => $bonusType,
                    'bonus_date' => carbonToday($date),
                    'bonus_amount' => $bonusAmount,
                    'level' => $level,
                    'purchase_id' => $purchase->id,
                    $isPrestasi ? 'should_upgrade' : 'should_ro' => $isPrestasi ? !$upline->has_package : !$upline->has_repeat_order,
                ]);

                $upline->notify(new BonusNotification($upline, 'database', ['driver' => 'mail', 'id' => $result->id]));
                $upline->notify(new BonusNotification($upline, 'database', ['driver' => 'onesender', 'id' => $result->id]));
            }

            $user = $upline;
            $level += 1;
        }
    }
    // static:end
}
