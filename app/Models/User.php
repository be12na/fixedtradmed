<?php

namespace App\Models;

use App\Casts\FullDatetimeCast;
use App\Casts\JsonableCast;
use App\Casts\MediumDatetimeCast;
use App\Models\Traits\ModelAddressTrait;
use App\Models\Traits\ModelIDTrait;
use App\Models\Traits\NetworksTrait;
use App\Models\Traits\UserBranchTrait;
use App\Models\Traits\UserOmzetTrait;
// use App\Models\Traits\UserPackageTrait;
use App\Models\Traits\UserPositionTrait;
use App\Models\Traits\UserReferralTrait;
use App\Models\Traits\UserRewardTrait;
use App\Models\Traits\UserShoppingTrait;
use App\Models\Traits\UserSocialMediaTrait;
use App\Models\Traits\UserStatusTrait;
use App\Models\Traits\UserTypeTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use ModelIDTrait,
        ModelAddressTrait,
        UserTypeTrait,
        UserStatusTrait,
        UserPositionTrait,
        UserBranchTrait,
        UserOmzetTrait,
        // UserPackageTrait,
        NetworksTrait,
        UserSocialMediaTrait,
        UserRewardTrait,
        UserShoppingTrait,
        UserReferralTrait;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'name',
        'email',
        'referral_id',
        'is_login',
        'user_group',
        'user_type',
        'division_id',
        'position_int',
        'manager_type',
        'position_ext',
        'upline_id',
        'level_id',
        'branch_id',
        'branch_manager',
        'user_status',
        'status_at',
        'status_logs',
        'activated',
        'activated_at',
        'phone',
        'mitra_type',
        'sub_domain',
        'market_name',
        'is_profile',
        'profile_at',
        'image_profile',
        'identity',
        'address',
        'village_id',
        'village',
        'district_id',
        'district',
        'city_id',
        'city',
        'province_id',
        'province',
        'pos_code',
        'valid_id',
        'contact_address',
        'contact_village_id',
        'contact_village',
        'contact_district_id',
        'contact_district',
        'contact_city_id',
        'contact_city',
        'contact_province_id',
        'contact_province',
        'contact_pos_code',
        'roles',
        'reg_by_ref',
        'mitra_type_reg',
        'session_id',
        'facebook',
        'tokopedia',
        'tiktok',
        'instagram',
        'shopee',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_login' => 'boolean',
        'is_profile' => 'boolean',
        'user_group' => 'integer',
        'user_type' => 'integer',
        'division_id' => 'integer',
        'position_int' => 'integer',
        'manager_type' => 'integer',
        'position_ext' => 'integer',
        'upline_id' => 'integer',
        'level_id' => 'integer',
        'mitra_type' => 'integer',
        'mitra_type_reg' => 'integer',
        'user_status' => 'integer',
        'status_at' => MediumDatetimeCast::class,
        'status_logs' => JsonableCast::class,
        'valid_id' => 'boolean',
        'activated' => 'boolean',
        'activated_at' => FullDatetimeCast::class,
        'roles' => JsonableCast::class,
    ];

    protected $defaultDomain = 'neo-domain';

    // public methode
    public function saveSession(string $sessionId): void
    {
        $this->session_id = $sessionId;
        $this->save();
    }

    public function totalOmzet(bool $includeSelf = null): int
    {
        if ($this->is_internal_member_user) {
            $includeSelf = is_null($includeSelf) ? false : $includeSelf;

            if ($includeSelf === true) {
                return $this->network_omzet_with_self;
            }

            return $this->network_omzet_without_self;
        } elseif ($this->is_member_mitra_user) {
            return $this->mitra_omzet;
        }

        return 0;
    }
    // end public methode

    // relationship
    public function banks()
    {
        return $this->hasMany(Bank::class, 'user_id', 'id');
    }

    public function memberActiveBank()
    {
        return $this->hasOne(Bank::class, 'user_id', 'id')
            ->byActive();
    }
    // relationship:end

    // model scope
    public function scopeByUsername(Builder $builder, $username): Builder
    {
        return $builder->where('username', '=', $username);
    }

    public function scopeByMitraDomain(Builder $builder, $domain): Builder
    {
        return $builder->where('sub_domain', '=', $domain);
    }

    public function scopeByMitraLevel(Builder $builder, int $level): Builder
    {
        return $builder->where('level_id', '=', $level);
    }

    public function scopeByHasProfile(Builder $builder): Builder
    {
        return $builder->where('is_profile', '=', true);
    }

    public function scopeBySearch(Builder $builder, string $search): Builder
    {
        return $builder->where(function ($src) use ($search) {
            $likeSearch = "%{$search}%";
            return $src->where('username', 'like', $likeSearch)
                ->orWhere('name', 'like', $likeSearch);
        });
    }

    public function scopeByMitraReferral(Builder $builder, $referralId): Builder
    {
        return $builder->byUsername($referralId)->byMemberGroup()->byStatus(USER_STATUS_ACTIVE)
            ->byInternalPosition(USER_INT_MGR);
    }
    // model scope

    // accessor
    public function getIsTopMemberAttribute()
    {
        return ($this->is_member_mitra_user && ($this->id == TOP_MEMBER_ID));
    }

    public function getPermissionNameAttribute()
    {
        $result = 'public.guest';
        if ($this->is_main_user) {
            $key = implode('.', [$this->user_group, $this->user_type]);
            $result = Arr::get(USER_GROUP_TYPES, $key, 'public.guest');
        } elseif ($this->is_member_user) {
            $keys = ['member'];

            if ($this->is_member_distributor_user) {
                $keys[] = 'distributor';
            } elseif ($this->is_member_agent_user) {
                $keys[] = 'agen';
            } elseif ($this->is_member_mitra_user) {
                $keys[] = 'mitra';
                $default = Arr::get(MITRA_RULES, MITRA_TYPE_DROPSHIPPER);
                $type = Arr::get(MITRA_RULES, $this->mitra_type, $default);
                $keys[] = strtolower($type);
            } else {
                $keys[] = 'member';
            }

            $result = implode('.', $keys);
        }

        return $result;
    }

    public function getPermissionGroupNameAttribute()
    {
        if ($this->is_member_mitra_user) {
            return 'mitra';
        } elseif ($this->is_member_customer_user) {
            return 'customer';
        }

        return explode('.', $this->permission_name)[0];
    }

    public function getRoleNameAttribute()
    {
        return explode('.', $this->permission_name)[1];
    }

    public function getIsMemberAgentUserAttribute()
    {
        return ($this->is_internal_member_user &&  !$this->is_member_distributor_user);
    }

    public function getStatusNameAttribute()
    {
        $statusName = 'Aktif';
        if ($this->user_status == USER_STATUS_BANNED) {
            $statusName = 'BANNED';
        } elseif ($this->user_status == USER_STATUS_NEED_ACTIVATE) {
            $statusName = 'Belum Aktifasi';
        } elseif ($this->user_status == USER_STATUS_INACTIVE) {
            $statusName = 'Tidak Aktif';
        }

        return $statusName;
    }

    public function getIsActiveUserAttribute()
    {
        return ($this->activated && ($this->user_status == USER_STATUS_ACTIVE));
    }

    public function getDefaultUserDomainAttribute()
    {
        return $this->defaultDomain;
    }

    public function getUserDomainAttribute()
    {
        if (!$this->is_member_mitra_user && !$this->is_member_customer_user) {
            if ($this->is_main_user) {
                return ($this->role_name == 'founder') ? $this->role_name : $this->permission_group_name;
            } else {
                return strtolower($this->internal_position_code);
            }
        } else {
            // return $this->sub_domain ?? $this->defaultDomain;
            return $this->username;
        }
    }

    public function getHasReferralLinkAttribute()
    {
        return ($this->position_int == USER_INT_MGR);
    }

    // khusus MGR
    public function getMitraReferralUrlAttribute()
    {
        if ($this->has_referral_link) {
            return route('referral-mitra.link', ['mitraReferral' => $this->username]);
        }

        return null;
    }

    // khusus mitra
    public function getIsMitraPremiumAttribute()
    {
        return ($this->is_member_mitra_user && ($this->mitra_type == MITRA_TYPE_AGENT));
    }

    public function getHasMitraReferralLinkAttribute()
    {
        // return $this->is_mitra_premium;
        return true;
    }

    // khusus mitra
    public function getMitraReferralLinkUrlAttribute()
    {
        // if ($this->has_mitra_referral_link) {
        //     return route('regMitraBasic.create', ['mitraPremium' => $this->username]);
        // }

        // return null;

        return route('regMitra.create', ['mitraReferral' => $this->username]);
    }

    // khusus mitra
    public function getMitraStoreUrlAttribute()
    {
        if ($this->is_member_mitra_user) {
            return route('mitraStore.index', ['mitraStore' => $this->username]);
        }

        return null;
    }

    public function getReferralNameAttribute()
    {
        // return $this->referral ? $this->referral->name : env('APP_COMPANY');
        return $this->referral ? $this->referral->name : '-';
    }

    public function getMitraTypeNameAttribute()
    {
        // return Arr::get(MITRA_TYPES, $this->mitra_type ?? 0);
        // return Arr::get(MITRA_NAMES, $this->mitra_type ?? 0);
        return $this->active_package
            ? $this->active_package->code
            : 'Dropshipper';
    }

    public function getLevelNameAttribute()
    {
        $array = $this->is_member_mitra_user ? MITRA_LEVELS : USER_LEVELS;
        return Arr::get($array, $this->level_id ?? 0);
    }

    public function getLayoutBladeAttribute()
    {
        $result = '';

        if ($this->is_main_user) {
            $result = '-main';
        } elseif ($this->is_member_mitra_user) {
            $result = '-mitra';
        } elseif ($this->is_member_user && !$this->is_member_mitra_user) {
            $result = '-member';
        }

        return "layouts.app{$result}";
    }

    public function getInternationalPhoneAttribute()
    {
        $phone = $this->phone;
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    public function getIsResellerAttribute()
    {
        return $this->has_package;
    }
    // end accessor
}
