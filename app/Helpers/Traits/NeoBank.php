<?php

namespace App\Helpers\Traits;

use App\Models\Bank;

trait NeoBank
{
    public function mainBanks(bool $activeOnly)
    {
        return Bank::getMainBanks($activeOnly);
    }
}