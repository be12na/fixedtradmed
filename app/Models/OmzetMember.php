<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OmzetMember extends Model
{
    use HasFactory;

    const CREATED_AT = null;
    const UPDATED_AT = null;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'omzet_date',
        'branch_id',
        'transfer_id',
        'transaction_id',
        'salesman_id',
        'salesman_ancestors',
        'is_from_mitra',
        'qty_box',
        'qty_pcs',
        'omzet',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'qty_box' => 'integer',
        'qty_pcs' => 'integer',
        'omzet' => 'integer',
        'is_from_mitra' => 'boolean',
        'salesman_ancestors' => 'array',
    ];

    // scope
    public function scopeByFromMitra(Builder $builder, bool $isFromMitra = null)
    {
        if (is_null($isFromMitra)) return $builder;

        return $builder->where('is_from_mitra', '=', $isFromMitra);
    }
}
