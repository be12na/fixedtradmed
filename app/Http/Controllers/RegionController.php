<?php

namespace App\Http\Controllers;

use App\Repositories\RegionRepository;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function region(Request $request)
    {
        $current = $request->get('current');
        $search = $request->get('search');
        $region = RegionRepository::getRegionFull($search, $current);

        return response()->json($region->map(function ($row) {
            return ['id' => $row->id, 'text' => $row->full_name];
        })->toArray());
    }

    public function province(Request $request)
    {
        $current = $request->get('current');
        $search = $request->get('search');
        $province = RegionRepository::getProvince($search, $current);

        return response()->json($province->map(function ($row) {
            return ['id' => $row->id, 'text' => $row->name];
        })->toArray());
    }

    public function city(Request $request)
    {
        $current = $request->get('current');
        $search = $request->get('search');
        $province = $request->get('parent');
        $city = RegionRepository::getCityRegency($province ?? '-', $search, $current);

        return response()->json($city->map(function ($row) {
            return ['id' => $row->id, 'text' => $row->name];
        })->toArray());
    }

    public function district(Request $request)
    {
        $current = $request->get('current');
        $search = $request->get('search');
        $city = $request->get('parent');
        $district = RegionRepository::getDistrict($city ?? '-', $search, $current);

        return response()->json($district->map(function ($row) {
            return ['id' => $row->id, 'text' => $row->name];
        })->toArray());
    }

    public function village(Request $request)
    {
        $current = $request->get('current');
        $search = $request->get('search');
        $district = $request->get('parent');
        $village = RegionRepository::getVillage($district ?? '-', $search, $current);

        return response()->json($village->map(function ($row) {
            return ['id' => $row->id, 'text' => $row->name];
        })->toArray());
    }
}
