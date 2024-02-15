<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class Select2Controller extends Controller
{
    public function managers(Request $request)
    {
        $branchId = intval($request->get('branch'));
        $currentId = intval($request->get('current'));
        $search = $request->get('search');
        $rows = User::byMemberGroup()->byInternalPosition(USER_INT_MGR);
        $current = null;

        if ($branchId > 0) {
            $rows = $rows->whereHas('branches', function ($has) use ($branchId) {
                return $has->where('branch_id', '=', $branchId);
            });
        }

        if ($currentId > 0) {
            $current = (clone $rows)->byId($currentId)->first();
            $rows = $rows->where('id', '!=', $currentId);
        }

        if (!empty($search) && ($search != '')) {
            $rows = $rows->where(function ($w) use ($search) {
                return $w->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $rows = $rows->orderBy('name')->get();
        $collect = collect();

        if (!empty($current)) $collect->push($current);

        foreach ($rows as $row) {
            $collect->push($row);
        }

        $result = [];
        $format = '<div><span>%s</span><span class="ms-1 fw-normal fst-italic">(%s)</span></div>';
        foreach ($collect as $col) {
            $result[] = [
                'id' => $col->id,
                'text' => $col->name . ' (' . $col->username . ')',
                'html' => sprintf($format, $col->name, $col->username),
            ];
        }

        return response()->json($result);
    }

    public function branchTeams(Request $request)
    {
        $branchId = intval($request->get('branch'));
        $currentId = intval($request->get('current'));
        $search = $request->get('search');
        $rows = User::byMemberGroup()->byInternalPosition(USER_INT_MGR, '>=');
        $current = null;

        if ($branchId > 0) {
            $rows = $rows->whereHas('branches', function ($has) use ($branchId) {
                return $has->where('branch_id', '=', $branchId);
            });
        }

        if ($currentId > 0) {
            $current = (clone $rows)->byId($currentId)->first();
            $rows = $rows->where('id', '!=', $currentId);
        }

        if (!empty($search) && ($search != '')) {
            $rows = $rows->where(function ($w) use ($search) {
                return $w->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $rows = $rows->orderBy('name')->get();
        $collect = collect();

        if (!empty($current)) $collect->push($current);

        foreach ($rows as $row) {
            $collect->push($row);
        }

        $result = [];
        $format = '<div><span>%s</span><span class="ms-1 fw-normal fst-italic">(%s)</span></div>';
        foreach ($collect as $col) {
            $result[] = [
                'id' => $col->id,
                'text' => $col->name . ' (' . $col->username . ')',
                'html' => sprintf($format, $col->name, $col->username),
            ];
        }

        return response()->json($result);
    }
}
