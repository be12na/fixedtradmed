<?php

namespace App\Models;

use App\Casts\FloatCast;
use App\Casts\FullDateCast;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class BranchTransfer extends Model
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
        'transfer_date',
        'branch_id',
        'manager_id',
        'manager_position',
        'manager_type',
        'bank_id',
        'bank_code',
        'bank_name',
        'account_no',
        'account_name',
        'total_omzets',
        'total_crews',
        'total_foundations',
        'total_savings',
        'sub_total_sales',
        'discount_persen',
        'discount_amount',
        'omzet_used',
        'sub_total',
        'unique_digit',
        'total_transfer',
        'image_transfer',
        'transfer_at',
        'transfer_status',
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
        'transfer_date' => FullDateCast::class,
        'total_omzets' => 'integer',
        'total_crews' => 'integer',
        'total_foundations' => 'integer',
        'total_savings' => 'integer',
        'sub_total_sales' => 'integer',
        'discount_persen' => FloatCast::class,
        'discount_amount' => 'integer',
        'omzet_used' => 'integer',
        'sub_total' => 'integer',
        'unique_digit' => 'integer',
        'total_transfer' => 'integer',
        'transfer_at' => 'datetime',
        'transfer_status' => 'integer',
        'status_at' => 'datetime',
    ];

    public const IMAGE_DISK = 'transfer';
    public const IMAGE_ASSET = 'images/uploads/transfers/';

    // static
    public static function makeCode($transferDate): string
    {
        if (!is_int($transferDate)) $transferDate = strtotime($transferDate);
        $date = date('Ymd', $transferDate);
        $randCode = str_pad((string) mt_rand(10001, 999999), 6, '0', STR_PAD_LEFT);

        return "TR{$date}{$randCode}";
    }

    // relation
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function transferDetails()
    {
        return $this->hasMany(BranchTransferDetail::class, 'transfer_id', 'id');
    }

    public function omzetLogs()
    {
        return $this->hasMany(OmzetMember::class, 'transfer_id', 'id')->byFromMitra(false);
    }

    // scope
    public function scopeByBranch(Builder $builder, $branch): Builder
    {
        $branchId = is_object($branch) ? $branch->id : $branch;

        return $builder->where('branch_id', '=', $branchId);
    }

    public function scopeInBranches(Builder $builder, array $branchIds): Builder
    {
        return $builder->whereIn('branch_id', $branchIds);
    }

    public function scopeByBetweenDate(Builder $builder, $start, $end): Builder
    {
        $dateStart = ($start instanceof Carbon) ? $start->format('Y-m-d') : $start;
        $dateEnd = ($end instanceof Carbon) ? $end->format('Y-m-d') : $end;

        return $builder->whereBetween('transfer_date', [$dateStart, $dateEnd]);
    }

    public function scopeByStatus(Builder $builder, array|int $status = null): Builder
    {
        if (is_null($status)) return $builder;
        if (is_array($status) && !empty($status)) {
            return $builder->whereIn('transfer_status', $status);
        }

        return $builder->where('transfer_status', '=', $status);
    }

    public function scopeByApproved(Builder $builder)
    {
        return $builder->byStatus(PROCESS_STATUS_APPROVED);
    }

    // accessor
    public function getManagerNameAttribute()
    {
        return $this->manager ? $this->manager->name : null;
    }

    public function getBranchNameAttribute()
    {
        return $this->branch ? $this->branch->name : null;
    }

    public function getStatusTextAttribute()
    {
        return Arr::get(PROCESS_STATUS_LIST, $this->transfer_status);
    }

    public function getImageUrlAttribute()
    {
        return $this->image_transfer ? asset(static::IMAGE_ASSET . $this->image_transfer) : '';
    }

    public function getMgrPositionAttribute()
    {
        return app('appStructure')->getDataById(false, $this->manager_position ?? -1);
    }

    public function getMgrPositionCodeAttribute()
    {
        return $this->mgr_position ? $this->mgr_position->code : null;
    }

    public function getMgrPositionNameAttribute()
    {
        return $this->mgr_position ? $this->mgr_position->name : null;
    }
}
