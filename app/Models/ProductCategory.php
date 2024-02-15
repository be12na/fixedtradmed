<?php

namespace App\Models;

use App\Casts\UppercaseCast;
use App\Models\Traits\ModelActiveTrait;
use App\Models\Traits\ModelCodeTrait;
use App\Models\Traits\ModelIDTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    use ModelIDTrait, ModelCodeTrait, ModelActiveTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'merek',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'code' => UppercaseCast::class,
        'is_active' => 'boolean',
    ];

    // static
    public static function firstByMerek(): self|null
    {
        return static::where('is_active', true)->orderBy('merek')->orderBy('name')->first();
    }

    // relation
    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id', 'id');
    }

    // scope
}
