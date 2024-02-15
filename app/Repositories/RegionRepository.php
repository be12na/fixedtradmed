<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RegionRepository extends BaseRepository
{
    public static function dbSeed(): void
    {
        $delimeter = ',';
        $filename = __DIR__ . "/csv/daerah.csv";
        $handle = fopen($filename, 'r');
        $datas = [];
        while (!feof($handle)) {
            $datas[] = fgetcsv($handle, 0, $delimeter);
        }
        fclose($handle);

        $provinces = [];
        $regencies = [];
        $chunkDistricts = [];
        $chunkVillages = [];
        $districtNo = 1;
        $districtChunkNo = 1;
        $villageNo = 1;
        $villageChunkNo = 1;

        // khusus untuk district dan village menggunakan metode chunk
        // supaya proses lebih cepat dan tidak berat dikarenakan jumlah data yg besar

        foreach ($datas as $data) {
            if ($data == false) continue;
            $lengthCode = count($codes = explode('.', $data[0]));
            if ($lengthCode == 1) {
                $test = explode(' ', $data[1]);
                if (in_array($test[0], ['DI', 'DKI'])) {
                    $data[1] = $test[0] . ' ' . ucwords(strtolower(implode(' ', array_slice($test, 1))));
                } else {
                    $data[1] = ucwords(strtolower($data[1]));
                }
                // province
                $provinces[] = [
                    'id' => $data[0],
                    'name' => $data[1]
                ];
            } elseif ($lengthCode == 2) {
                // regency
                $propince_id = $codes[0];
                $id = implode('', $codes);
                $regencies[] = [
                    'id' => $id,
                    'province_id' => $propince_id,
                    'name' => ucwords(strtolower($data[1]))
                ];
            } elseif ($lengthCode == 3) {
                // district
                // pastikan kode district adalah 3 digit, sehingga ketika digabung menjadi 7
                $codes[2] = str_pad($codes[2], 3, '0', STR_PAD_LEFT);
                $regency_id = implode('', array_slice($codes, 0, 2));
                $id = implode('', $codes);

                if ($districtNo > 1000) {
                    $districtNo = 1;
                    $districtChunkNo += 1;
                }

                $chunkDistricts[$districtChunkNo][] = [
                    'id' => $id,
                    'regency_id' => $regency_id,
                    'name' => $data[1]
                ];

                $districtNo += 1;
            } elseif ($lengthCode == 4) {
                // village
                // pastikan kode district adalah 3 digit, sehingga ketika digabung menjadi 7
                $codes[2] = str_pad($codes[2], 3, '0', STR_PAD_LEFT);
                // pastikan juga kode village adalah 4 digit, sehingga jika semua digabung adalah 11 digit
                $codes[3] = str_pad($codes[3], 4, '0', STR_PAD_LEFT);
                $district_id = implode('', array_slice($codes, 0, 3));
                $id = implode('', $codes);

                if ($villageNo > 1000) {
                    $villageNo = 1;
                    $villageChunkNo += 1;
                }

                $chunkVillages[$villageChunkNo][] = [
                    'id' => $id,
                    'district_id' => $district_id,
                    'name' => $data[1]
                ];

                $villageNo += 1;
            } else {
                continue;
            }
        }

        // jika sudah ada datanya, tak ada proses
        if (DB::table('provinces')->get()->isEmpty()) {
            // insert provinces
            DB::table('provinces')->insert($provinces);
            // insert regencies
            DB::table('regencies')->insert($regencies);
            // insert districts
            foreach ($chunkDistricts as $districts) {
                DB::table('districts')->insert($districts);
            }
            // insert villages
            foreach ($chunkVillages as $villages) {
                DB::table('villages')->insert($villages);
            }
        }
    }

    public static function getRegionFull(string $search = null, string $current = null): Collection
    {
        $query = DB::table('provinces')
            ->join('regencies', 'regencies.province_id', '=', 'provinces.id')
            ->join('districts', 'districts.regency_id', '=', 'regencies.id')
            ->join('villages', 'villages.district_id', '=', 'districts.id')
            ->selectRaw("villages.id, concat(villages.name, ', Kecamatan ', districts.name, ', ', regencies.name, ', Propinsi ', provinces.name) as full_name");

        if (!is_null($search) && ($search != '')) {
            $query = $query->where(function ($q) use ($search) {
                $q->where('villages.name', 'like', "%{$search}%")
                    ->orWhere('districts.name', 'like', "%{$search}%")
                    ->orWhere('regencies.name', 'like', "%{$search}%")
                    ->orWhere('provinces.name', 'like', "%{$search}%")
                    ->orWhere(DB::raw("concat(villages.name, ', ', districts.name, ', ', regencies.name, ', ', provinces.name)"), 'like', "%{$search}%");
            });

            if (!is_null($current)) {
                $query = $query->orWhere('villages.id', '=', $current);
            }
        } else {
            if (!is_null($current)) {
                $data = self::getRegionFullByVillageId($current);
                if (!empty($data)) {
                    $query = $query->where('provinces.id', '=', $data->province_id)
                        ->where('regencies.id', '=', $data->regency_id);
                }
            }
        }

        if (!is_null($current)) {
            $query = $query->orderBy(DB::raw("(case when villages.id='{$current}' then 0 else 1 end)"));
        }

        $query = $query->orderBy('provinces.name')
            ->orderBy('regencies.name')
            ->orderBy('districts.name')
            ->orderBy('villages.name');

        return $query->limit(10)->get();
    }

    public static function getRegionFullByVillageId($villageId)
    {
        return DB::table('provinces')
            ->join('regencies', 'regencies.province_id', '=', 'provinces.id')
            ->join('districts', 'districts.regency_id', '=', 'regencies.id')
            ->join('villages', 'villages.district_id', '=', 'districts.id')
            ->selectRaw("villages.id, villages.district_id, districts.regency_id, regencies.province_id, concat(villages.name, ', ', districts.name, ', ', regencies.name, ', ', provinces.name) as full_name")
            ->where('villages.id', '=', $villageId)
            ->first();
    }

    public static function getProvince(string $search = null, string $current = null): Collection
    {
        $query = DB::table('provinces');

        if (!is_null($search) && ($search != '')) {
            if (!is_null($current)) {
                $query = $query->where(function ($qry) use ($search, $current) {
                    return $qry->where('name', 'like', "%{$search}%")
                        ->orWhere('id', '=', $current);
                });
            } else {
                $query = $query->where('name', 'like', "%{$search}%");
            }
        }

        if (!is_null($current)) {
            $query = $query->orderBy(DB::raw("(case when id='{$current}' then 0 else 1 end)"));
        }

        return $query->orderBy('name')->limit(10)->get();
    }

    public static function getProvinceById($dataId)
    {
        return DB::table('provinces')->where('id', '=', $dataId)->first();
    }

    public static function getCityRegency($province, string $search = null, string $current = null): Collection
    {
        $province_id = self::dataId($province);
        $query = DB::table('regencies')
            ->where('province_id', '=', $province_id);

        if (!is_null($search) && ($search != '')) {
            if (!is_null($current)) {
                $query = $query->where(function ($qry) use ($search, $current) {
                    return $qry->where('name', 'like', "%{$search}%")
                        ->orWhere('id', '=', $current);
                });
            } else {
                $query = $query->where('name', 'like', "%{$search}%");
            }
        }

        if (!is_null($current)) {
            $query = $query->orderBy(DB::raw("(case when id='{$current}' then 0 else 1 end)"));
        }

        return $query->orderBy('name')->limit(10)->get();
    }

    public static function getCityRegencyById($dataId, array $with = null)
    {
        $result = DB::table('regencies')->where('id', '=', $dataId)->first();

        if (!empty($result) && !empty($with)) {
            if (in_array('province', $with)) {
                $result->province = self::getProvinceById($result->province_id);
            }
        }

        return $result;
    }

    public static function getDistrict($city, string $search = null, string $current = null): Collection
    {
        $regency_id = self::dataId($city);
        $query = DB::table('districts')
            ->where('regency_id', '=', $regency_id);

        if (!is_null($search) && ($search != '')) {
            if (!is_null($current)) {
                $query = $query->where(function ($qry) use ($search, $current) {
                    return $qry->where('name', 'like', "%{$search}%")
                        ->orWhere('id', '=', $current);
                });
            } else {
                $query = $query->where('name', 'like', "%{$search}%");
            }
        }

        if (!is_null($current)) {
            $query = $query->orderBy(DB::raw("(case when id='{$current}' then 0 else 1 end)"));
        }

        return $query->orderBy('name')->limit(10)->get();
    }

    public static function getDistrictById($dataId, array $with = null)
    {
        $result = DB::table('districts')->where('id', '=', $dataId)->first();

        if (!empty($result) && !empty($with)) {
            if (in_array('regency', $with)) {
                $result->regency = self::getCityRegencyById($result->regency_id, $with);
            } elseif (in_array('city', $with)) {
                $result->city = self::getCityRegencyById($result->regency_id, $with);
            }
        }

        return $result;
    }

    public static function getVillage($district, string $search = null, string $current = null): Collection
    {
        $district_id = self::dataId($district);
        $query = DB::table('villages')
            ->where('district_id', '=', $district_id);

        if (!is_null($search) && ($search != '')) {
            if (!is_null($current)) {
                $query = $query->where(function ($qry) use ($search, $current) {
                    return $qry->where('name', 'like', "%{$search}%")
                        ->orWhere('id', '=', $current);
                });
            } else {
                $query = $query->where('name', 'like', "%{$search}%");
            }
        }

        if (!is_null($current)) {
            $query = $query->orderBy(DB::raw("(case when id='{$current}' then 0 else 1 end)"));
        }

        return $query->orderBy('name')->limit(10)->get();
    }

    public static function getVillageById($dataId, array $with = null)
    {
        $result = DB::table('villages')->where('id', '=', $dataId)->first();

        if (!empty($result) && !empty($with)) {
            if (in_array('district', $with)) {
                $result->district = self::getDistrictById($result->district_id, $with);
            }
        }

        return $result;
    }
}
