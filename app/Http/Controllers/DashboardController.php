<?php

namespace App\Http\Controllers;

use App\Helpers\Neo;
use App\Http\Controllers\Main\DashboardTrait as MainDashboard;
use App\Http\Controllers\Member\DashboardTrait as MemberDashboard;
use App\Http\Controllers\Mitra\DashboardTrait as MitraDashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use MainDashboard, MemberDashboard, MitraDashboard;

    protected Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        return $user->is_main_user ? $this->mainDashboard($request, $this->neo) : $this->mitraDashboard($request, $this->neo);

        // if ($user->is_main_user) {
        //     return $this->mainDashboard($request, $this->neo);
        // } elseif ($user->is_member_mitra_user) {
        //     return $this->mitraDashboard($request, $this->neo);
        // }

        // return $this->memberDashboard($request, $this->neo);
    }
}
