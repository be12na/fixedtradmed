<?php

namespace App\Models;

use App\Casts\FullDateCast;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Nette\Utils\Random;

class BranchPayment extends Model
{
    use HasFactory, SoftDeletes;
    use ModelIDTrait, ModelCodeTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'payment_date',
        'branch_id',
        'manager_id',
        'bank_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'total_price',
        'total_discount',
        'sub_total',
        'unique_digit',
        'total_transfer',
        'image_transfer',
        'transfer_at',
        'transfer_status',
        'payment_note',
        'status_at',
        'status_by',
        'status_note',
        'transfer_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date' => FullDateCast::class,
        'total_price' => 'integer',
        'total_discount' => 'integer',
        'sub_total' => 'integer',
        'unique_digit' => 'integer',
        'total_transfer' => 'integer',
        'transfer_at' => 'datetime',
        'transfer_status' => 'integer',
        'status_at' => 'datetime',
    ];

    public const IMAGE_DISK = 'transfer';
    public const IMAGE_ASSET = 'images/uploads/transfers/';

    // relations
    public function details()
    {
        return $this->hasMany(BranchPaymentDetail::class, 'branch_payment_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    // scope
    public function scopeByTransferable(Builder $builder): Builder
    {
        return $builder->whereIn('transfer_status', [PAYMENT_STATUS_PENDING, PAYMENT_STATUS_REJECTED]);
    }

    public function scopeByModifiable(Builder $builder): Builder
    {
        return $builder->byTransferable();
    }

    public function scopeByApproving(Builder $builder): Builder
    {
        return $builder->where('transfer_status', '=', PAYMENT_STATUS_TRANSFERRED);
    }

    // static
    public static function makeCode($paymentDate): string
    {
        if (!is_int($paymentDate)) $paymentDate = strtotime($paymentDate);
        $date = date('Ymd', $paymentDate);
        $randCode = Random::generate(6, '0-9');

        return "PM-{$date}{$randCode}";
    }

    // accessor
    public function getIsTransferableAttribute()
    {
        return in_array($this->transfer_status, [PAYMENT_STATUS_PENDING, PAYMENT_STATUS_REJECTED]);
    }

    public function getIsApprovedAttribute()
    {
        return ($this->transfer_status == PAYMENT_STATUS_APPROVED);
    }

    public function getIsApprovingAttribute()
    {
        return ($this->transfer_status == PAYMENT_STATUS_TRANSFERRED);
    }

    public function getTransferStatusNameAttribute()
    {
        return Arr::get(PAYMENT_STATUS_LIST, $this->transfer_status);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_transfer ? asset(static::IMAGE_ASSET . $this->image_transfer) : '';
    }
}
