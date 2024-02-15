<?php

use App\Helpers\Neo;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

if (!function_exists('hasPermission')) {
    function hasPermission($routeName, \App\Models\User $user = null)
    {
        return app('appPermission')->hasPermission($routeName, $user);
    }
}

if (!function_exists('neo')) {
    function neo(): Neo
    {
        return app('neo');
    }
}

if (!function_exists('isLive')) {
    function isLive()
    {
        return neo()->isLive();
    }
}

if (!function_exists('memberStatusText')) {
    function memberStatusText($user)
    {
        $user = optional($user);

        if (!boolval($user->activated)) return 'Belum Aktif';
        if ($user->user_status != USER_STATUS_ACTIVE) return 'Tidak Aktif';

        return boolval($user->is_login) ? 'Aktif' : 'Diblokir';
    }
}

if (!function_exists('memberStatusColor')) {
    function memberStatusColor($user, string $trueColor = null, string $falseColor = null)
    {
        $falseColor = is_null($falseColor) ? 'text-danger' : $falseColor;
        $trueColor = is_null($trueColor) ? 'text-success' : $trueColor;

        $user = optional($user);

        if (!boolval($user->activated)) return $falseColor;
        if ($user->user_status != USER_STATUS_ACTIVE) return $falseColor;

        return boolval($user->is_login) ? $trueColor : $falseColor;
    }
}

if (!function_exists('canStockOpname')) {
    function canStockOpname()
    {
        return neo()->canStockOpname();
    }
}

if (!function_exists('canSale')) {
    function canSale()
    {
        return neo()->canSale();
    }
}

if (!function_exists('isMitraPremium')) {
    function isMitraPremium(\App\Models\User $user = null): bool
    {
        if (empty($user)) return false;

        return $user->is_mitra_premium;
    }
}

if (!function_exists('authIsMitraPremium')) {
    function authIsMitraPremium(): bool
    {
        $user = auth()->user();

        return isMitraPremium($user);
    }
}

if (!function_exists('isManager')) {
    function isManager(\App\Models\User $user = null): bool
    {
        if (empty($user)) return false;

        return $user->is_manager_user;
    }
}

if (!function_exists('isManagerDistributor')) {
    function isManagerDistributor(\App\Models\User $user = null): bool
    {
        if (empty($user)) return false;

        return $user->is_member_distributor_user;
    }
}

if (!function_exists('authIsManager')) {
    function authIsManager(): bool
    {
        $user = auth()->user();

        return isManager($user);
    }
}

if (!function_exists('authIsManagerDistributor')) {
    function authIsManagerDistributor(): bool
    {
        $user = auth()->user();

        return isManagerDistributor($user);
    }
}

if (!function_exists('matchMenu')) {
    function matchMenu($requireNames): bool
    {
        $match = false;
        $requiredList = is_array($requireNames) ? $requireNames : [$requireNames];
        $explodedName = explode('.', $currentName = \Illuminate\Support\Facades\Route::currentRouteName());
        if (!empty($currentName) && !empty($requiredList)) {
            $tmpName = '';
            $tmpList = [];
            foreach ($explodedName as $name) {
                $tmpName .= ".{$name}";
                $tmpName = ltrim($tmpName, '.');
                $tmpList[] = $tmpName;
            }

            foreach ($tmpList as $nameInList) {
                if (in_array($nameInList, $requiredList)) {
                    $match = true;
                    break;
                }
            }
        }

        return $match;
    }
}

if (!function_exists('carbonToday')) {
    function carbonToday(Carbon $date = null): Carbon
    {
        return is_null($date) ? Carbon::today() : $date->startOfDay();
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($value, $decimals = 0): string
    {
        return number_format($value, $decimals, __('format.decimal'), __('format.thousand'));
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency($value, $decimalLength = 2, $startSymbol = true, $endSymbol = false): string
    {
        $isId = (config('app.locale') == 'id');
        $decimals = $isId ? 0 : $decimalLength;
        $result = formatNumber($value, $decimals);
        $result = '<span class="d-inline-block">' . $result . '</span>';
        $symbolStart = '';
        $symbolEnd = '';

        if ($startSymbol === true) {
            $symbolStart = __('format.currency.symbol.text');
            if (!empty($symbolStart)) {
                $symbolStart = '<sup class="me-1 fw-normal">' . trim($symbolStart, ' ') . '</sup>';
            } else {
                $symbolStart = '';
            }
        }

        if (($endSymbol === true) && $isId) {
            $symbolEnd = __('format.currency.symbol.end');
            if (!empty($symbolEnd)) {
                $symbolEnd = '<span class="me-1">' . trim($symbolEnd, ' ') . '</span>';
            } else {
                $symbolEnd = '';
            }
        }

        return new HtmlString($symbolStart . $result . $symbolEnd);
    }
}

if (!function_exists('formatAutoNumber')) {
    function formatAutoNumber($value, bool $asCurrency, $maxDecimalLength = 2, $currencyStartSymbol = true, $currencyEndSymbol = false): string
    {
        $exactValue = floatval($value);
        $values = explode('.', strval($exactValue));
        $decimalValue = (count($values) > 1) ? intval($values[1]) : 0;
        $maxDecimalLength = abs($maxDecimalLength);

        if ($asCurrency === true) {
            return formatCurrency($exactValue, $maxDecimalLength, $currencyStartSymbol, $currencyEndSymbol);
        }

        $lenDecimal = ($decimalValue == 0) ? 0 : strlen(strval($decimalValue));
        if ($lenDecimal > $maxDecimalLength) $lenDecimal = $maxDecimalLength;

        return formatNumber($exactValue, $lenDecimal);
    }
}

if (!function_exists('resolveTranslatedDate')) {
    function resolveTranslatedDate(string $date, string $sparator = null)
    {
        if (empty($date)) return $date;
        if (is_null($sparator)) $sparator = ' ';
        $arrDate = explode($sparator, $date);
        $test = [];
        foreach ($arrDate as $tt) {
            $res = $tt;
            if (!is_numeric($res)) {
                $res = strtolower($res);
                $res = ucfirst(Arr::get(DATE_ID, $res, $tt));
            }

            $test[] = $res;
        }

        return implode($sparator, $test);
    }
}

if (!function_exists('formatDatetime')) {
    function formatDatetime($time, string $format = null)
    {
        if (empty($time)) return null;
        if (empty($format)) $format = __('format.date.short') . ' H:i:s';

        $carbon = $time;
        if (!($time instanceof Carbon)) {
            if (!is_int($time)) $time = strtotime($time);

            $carbon = Carbon::createFromTimestamp($time);
        }

        return $carbon->translatedFormat($format);
    }
}

if (!function_exists('formatMediumDatetime')) {
    function formatMediumDatetime($time)
    {
        return formatDatetime($time, __('format.date.medium') . ' H:i:s');
    }
}

if (!function_exists('formatFullDatetime')) {
    function formatFullDatetime($time)
    {
        return formatDatetime($time, __('format.date.full') . ' H:i:s');
    }
}

if (!function_exists('formatShortDate')) {
    function formatShortDate($time)
    {
        return formatDatetime($time, __('format.date.short'));
    }
}

if (!function_exists('formatMediumDate')) {
    function formatMediumDate($time)
    {
        return formatDatetime($time, __('format.date.medium'));
    }
}

if (!function_exists('formatFullDate')) {
    function formatFullDate($time)
    {
        return formatDatetime($time, __('format.date.full'));
    }
}

if (!function_exists('weekNumber')) {
    function weekNumber(int $time = null)
    {
        $carbon = empty($time) ? Carbon::now() : Carbon::createFromTimestamp($time);

        return $carbon->week;
    }
}

if (!function_exists('datesOfWeek')) {
    function datesOfWeek(int $week = null, int $year = null, string $format = null): stdClass
    {
        if (is_null($week)) $week = weekNumber();
        $week = $week - 1;

        $carbon = Carbon::now(config('app.timezone'));
        if (is_null($year)) $year = $carbon->year;
        $carbon = $carbon->setISODate($year, $week);

        if (is_null($format)) $format = __('format.date.medium') . ' H:i:s';
        $carbonStart = (clone $carbon)->startOfWeek();
        $carbonEnd = (clone $carbon)->endOfWeek();

        return (object) [
            'carbonStart' => $carbonStart,
            'carbonEnd' => $carbonEnd,
            'start' => $carbonStart->translatedFormat($format),
            'end' => $carbonEnd->translatedFormat($format),
            'starts' => [
                'd' => $carbonStart->format('d'),
                'j' => $carbonStart->format('j'),
                'm' => $carbonStart->format('m'),
                'M' => $carbonStart->shortMonthName,
                'F' => $carbonStart->monthName,
                'Y' => $carbonStart->year,
                't' => $carbonStart->format('H:i:s'),
            ],
            'ends' => [
                'd' => $carbonEnd->format('d'),
                'j' => $carbonEnd->format('j'),
                'm' => $carbonEnd->format('m'),
                'M' => $carbonEnd->shortMonthName,
                'F' => $carbonEnd->monthName,
                'Y' => $carbonEnd->year,
                't' => $carbonEnd->format('H:i:s'),
            ],
        ];
    }
}

if (!function_exists('dayName')) {
    function dayName($time)
    {
        $carbon = $time;
        if (!($time instanceof Carbon)) {
            if (!is_int($time)) $time = strtotime($time);

            $carbon = Carbon::createFromTimestamp($time);
        }

        return $carbon->dayName;
    }
}

if (!function_exists('optionSelected')) {
    function optionSelected($data_value, $true_value)
    {
        $data_value = (string) $data_value;
        $true_value = (string) $true_value;

        return ($data_value == $true_value) ? 'selected' : '';
    }
}

if (!function_exists('checkboxChecked')) {
    function checkboxChecked($data, $field, $true_value = 1, $default = false, $in_error = false)
    {
        $value = isset($field) ? old($field) : null;
        if ($in_error) {
            return $value ? 'checked' : '';
        }
        if (isset($value)) {
            return ($value == $true_value) ? 'checked' : '';
        } else {
            if (isset($data)) {
                if (isset($field)) {
                    return ($data->$field == $true_value) ? 'checked' : '';
                }
            }
        }

        return $default ? 'checked' : '';
    }
}

if (!function_exists('contentCheck')) {
    function contentCheck(bool $value)
    {
        return ($value === true) ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>';
    }
}

if (!function_exists('ajaxError')) {
    function ajaxError(string $message = null, int $status = 500)
    {
        if (empty($message)) $message = 'Error';

        return response("<h3 class=\"bg-white text-danger\">{$message}</h3>", $status);
    }
}

if (!function_exists('pageError')) {
    function pageError(string $message = null, Closure|string $redirectRoute = null, array $routeParams = null)
    {
        if (empty($message)) $message = 'Page Error';

        if ($redirectRoute instanceof Closure) {
            return $redirectRoute($message);
        }

        if (empty($redirectRoute)) $redirectRoute = 'dashboard';
        if (is_null($routeParams)) $routeParams = [];

        return redirect()->route($redirectRoute, $routeParams)
            ->with('message', $message)
            ->with('messageClass', 'danger');
    }
}

if (!function_exists('fallbackRouteBinding')) {
    function fallbackRouteBinding($data, string $fallbackMessage = '', Closure|string $fallbackRoute = null, array $fallbackRouteParams = null)
    {
        if (!empty($data)) return $data;

        if (empty($fallbackMessage)) $fallbackMessage = 'Data tidak ditemukan.';
        if (is_null($fallbackRouteParams)) $fallbackRouteParams = [];

        throw new HttpResponseException(
            request()->ajax()
                ? ajaxError($fallbackMessage, 404)
                : pageError($fallbackMessage, $fallbackRoute, $fallbackRouteParams)
        );
    }
}

if (!function_exists('switchValueOfVars')) {
    function switchValueOfVars(&$var1, &$var2): void
    {
        list($var1, $var2) = [$var2, $var1];
    }
}

if (!function_exists('var1LowerEqualVar2')) {
    function var1LowestEqualVar2($lowValue, $highValue, array $vars): array
    {
        $varCount = count($vars);
        $result = $vars;

        if (($lowValue > $highValue) && ($varCount > 1)) {
            for ($i = 0; $i < count($vars); $i++) {
                $var1 = $vars[$i];
                if (!is_array($var1) && isset($vars[$i + 1])) {
                    $var2 = $vars[$i + 1];

                    switchValueOfVars($var1, $var2);

                    $result[$i] = $var1;
                    $result[$i + 1] = $var2;

                    $i = $i + 1;
                } else {
                    if (count($var1) != 2) continue;
                    $var1 = $vars[$i][0];
                    $var2 = $vars[$i][1];

                    switchValueOfVars($var1, $var2);

                    $result[$i] = [$var1, $var2];
                }
            }
        }

        return $result;
    }
}

if (!function_exists('isAppV2')) {
    function isAppV2(): bool
    {
        return false;
    }
}
