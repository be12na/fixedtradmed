<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchTransferDetail extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transfer_id',
        'sale_id',
        'sale_total_price',
        'sale_total_crew',
        'sale_total_foundation',
        'sale_savings',
    ];

    public function branchTransfer()
    {
        return $this->belongsTo(BranchTransfer::class, 'transfer_id', 'id');
    }

    public function branchSale()
    {
        return $this->belongsTo(BranchSale::class, 'sale_id', 'id');
    }
}
