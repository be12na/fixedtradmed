<?php

namespace App\Casts;

use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DateStartStockCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (empty($value)) return $value;

        return formatFullDate(strtotime($value));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return mixed
     */
    public function set($model, string $key, $value, array $attributes)
    {
        $date = $value ?? Carbon::today();
        if (!($date instanceof Carbon)) {
            $time = is_int($date) ? $date : strtotime($date);
            $date = Carbon::createFromTimestamp($time);
        }

        $date = $date->startOfWeek(DAY_STOCKOPNAME_START);

        return $date->format('Y-m-d');
    }
}
