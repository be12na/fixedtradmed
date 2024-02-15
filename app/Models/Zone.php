<?php

namespace App\Models;

use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    // relation
    public function deliveryArea()
    {
        return $this->hasMany(ZoneDelivery::class, 'zone_id', 'id');
    }

    // accessor
    public function getFullNameAttribute()
    {
        $name = $this->name;
        if (substr(strtolower($name), 0, 4) == 'zona') return $name;

        return "Zona {$name}";
    }
}
