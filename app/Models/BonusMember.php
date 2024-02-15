<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Casts\FullDateCast;
use App\Casts\JsonableCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonusMember extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'position_id',
        'bonus_type',
        'bonus_date',
        'is_internal',
        'level_id',
        'bonus_base',
        'bonus_percent',
        'bonus_amount',
        'setting_id',
        'transfer_id',
        'transaction_id',
        'item_id',
        'qty_box',
        'qty_pcs',
        'details',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bonus_type' => 'integer',
        'bonus_date' => FullDateCast::class,
        'is_internal' => 'boolean',
        'position_id' => 'integer',
        'level_id' => 'integer',
        'bonus_base' => 'integer',
        'bonus_percent' => FloatCast::class,
        'bonus_amount' => FloatCast::class,
        'qty_box' => 'integer',
        'qty_pcs' => 'integer',
        'details' => JsonableCast::class,
    ];
}
