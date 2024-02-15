<?php

namespace App\Helpers\Traits;

use stdClass;

trait NeoChart
{
    protected function setColorGraph(int $countRows):stdClass
    {
        $colors = [
            'rgba(220, 53, 69, %s)',
            'rgba(253, 126, 20, %s)',
            'rgba(255, 193, 7, %s)',
            'rgba(25, 135, 84, %s)',
            'rgba(13, 202, 240, %s)',
            'rgba(13, 110, 253, %s)',
            'rgba(111, 66, 193, %s)'
        ];

        $colorUrut = 0;
        $result = (object) [
            'background_color' => [],
            'border_color' => [],
        ];
        
        for($i = 0; $i < $countRows; $i++) {
            if ($colorUrut > 6) $colorUrut = 0;
            $selectColor = $colors[$colorUrut];
            $result->background_color[] = sprintf($selectColor, '0.5');
            $result->border_color[] = sprintf($selectColor, '1');
            $colorUrut++;
        }

        return $result;
    }
}