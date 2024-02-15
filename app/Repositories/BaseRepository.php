<?php

namespace App\Repositories;

class BaseRepository
{
    protected static function dataId($data)
    {
        return is_null($data) ? '' : (is_object($data) ? $data->id : $data);
    }

    protected static function prepareLog($oldData, array $newData, $skips = []): array
    {
        $arrayOldData = (array) $oldData;
        $arrSkips = array_merge(['id', 'url_id'], $skips);

        $result = [
            'oldData' => [],
            'newData' => []
        ];
        foreach ($arrayOldData as $key => $value) {
            if (is_object($value) || is_array($value) || in_array($key, $arrSkips)) continue;
            $result['oldData'][$key] = $value;
            $result['newData'][$key] = array_key_exists($key, $newData) ? $newData[$key] : $value;
        }

        return $result;
    }
}
