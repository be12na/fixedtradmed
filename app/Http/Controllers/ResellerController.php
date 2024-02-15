<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\RegionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class ResellerController extends Controller
{
    public function index(Request $request)
    {
        $values = $request->only(['prov', 'city', 'search']);
        $provinceId = Arr::get($values, 'prov');
        $province = $provinceId ? RegionRepository::getProvinceById($provinceId) : null;
        $cityId = Arr::get($values, 'city');
        $city = ($province && $cityId) ? RegionRepository::getCityRegencyById($cityId) : null;
        $search = Arr::get($values, 'search');

        if (empty($search) && empty($province) && empty($city) && !empty($values)) {
            return redirect()->route('reseller.index');
        }

        return view('public.reseller', [
            'province' => $province,
            'city' => $city,
            'search' => $search,
        ]);
    }

    public function dataTable(Request $request)
    {
        $reseller = User::query()
            ->byHasProfile()
            ->byMitraGroup()
            ->byMitraReseller()
            ->byStatus(USER_STATUS_ACTIVE)
            ->byActivated(true);

        if ($province = $request->get('prov')) {
            $reseller = $reseller->where('province_id', '=', $province);
        }

        if ($city = $request->get('city')) {
            $reseller = $reseller->where('city_id', '=', $city);
        }

        if ($search = $request->get('src')) {
            $reseller = $reseller->bySearch($search);
        }

        return datatables()->eloquent($reseller)
            ->addColumn('reseller', function ($row) {
                return new HtmlString(view('public.reseller-content', [
                    'user' => $row
                ])->render());
            })
            ->escapeColumns()
            ->toJson();
    }
}
