<?php

namespace App\Http\Controllers\Main;

use App\Helpers\Neo;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Main\Reports\BonusDistributor;
use App\Http\Controllers\Main\Reports\GlobalDetailManager;
use App\Http\Controllers\Main\Reports\GlobalManager;
use App\Http\Controllers\Main\Reports\GlobalProduct;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    use GlobalProduct, GlobalManager, GlobalDetailManager;
    use BonusDistributor;

    protected Neo $neo;

    public function __construct()
    {
        $this->neo = app('neo');
    }

    public function index(Request $request)
    {
        // 
    }

    public function indexGlobal(Request $request)
    {
        // 
    }

    public function indexBonus(Request $request)
    {
        // 
    }
}
