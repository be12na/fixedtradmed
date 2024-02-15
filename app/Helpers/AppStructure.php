<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class AppStructure
{
    private Collection $internalPositions;
    private Collection $externalPositions;
    private Collection $internalCollection;
    private Collection $externalCollection;

    public function __construct()
    {
        $this->internalPositions = $this->setAllPosition(true);
        $this->externalPositions = $this->setAllPosition(false);

        $this->internalCollection = $this->setDataCollection(true);
        $this->externalCollection = $this->setDataCollection(false);
    }

    private function setAllPosition(bool $internal): Collection
    {
        $positions = ($internal === true) ? USER_INT_POSITIONS : USER_EXT_POSITIONS;
        $result = collect();

        foreach ($positions as $key => $values) {
            $result->push((object) ['id' => $key, 'name' => $values[0], 'code' => $values[1]]);
        }

        return $result;
    }

    private function setDataCollection(bool $internal): Collection
    {
        $structures = ($internal === true) ? USER_INT_STRUCTURES : USER_EXT_STRUCTURES;
        $positions = ($internal === true) ? USER_INT_POSITIONS : USER_EXT_POSITIONS;
        $uplines = ($internal === true) ? USER_INT_UPLINES : USER_EXT_UPLINES;

        $result = collect();

        foreach ($structures as $key => $values) {
            $names = Arr::get($positions, $key);
            if (empty($names[0])) continue;
            $data = (object) [
                'id' => $key,
                'name' => $names[0],
                'code' => $names[1],
                'upline' => Arr::get($uplines, $key)
            ];

            $result->push($data);
        }

        return $result;
    }

    public function getAllPositions(bool $internal): Collection
    {
        return ($internal === true) ? $this->internalPositions : $this->externalPositions;
    }

    public function getPositionById(bool $internal, int $id)
    {
        return (($internal === true) ? $this->internalPositions : $this->externalPositions)->where('id', '=', $id)->first();
    }

    public function getSqlPositions(bool $internal, array $onlyPositions = null): string
    {
        $positions = $this->getAllPositions($internal);
        if (!empty($onlyPositions)) $positions = $positions->whereIn('id', $onlyPositions);

        $selects = [];
        if ($positions->isEmpty()) {
            $selects[] = "select -1 as id, null as name, null as code";
        } else {
            foreach ($positions as $position) {
                $positionName = $position->name;
                $selects[] = "select {$position->id} as id, '{$positionName}' as name, '{$position->code}' as code";
            }
        }

        return implode(' UNION ', $selects);
    }

    public function namePositionById(bool $internal, int $id)
    {
        $data = $this->getPositionById($internal, $id);
        return $data ? $data->name : null;
    }

    public function getAllData(bool $internal): Collection
    {
        return ($internal === true) ? $this->internalCollection : $this->externalCollection;
    }

    public function getDataById(bool $internal, int $id)
    {
        return (($internal === true) ? $this->internalCollection : $this->externalCollection)->where('id', '=', $id)->first();
    }

    public function getDataByCode(bool $internal, string $code)
    {
        return (($internal === true) ? $this->internalCollection : $this->externalCollection)->where('code', '=', strtoupper($code))->first();
    }

    public function getDataByName(bool $internal, string $name)
    {
        return (($internal === true) ? $this->internalCollection : $this->externalCollection)->where('name', '=', $name)->first();
    }

    public function getDataByUpline(bool $internal, $upline): Collection
    {
        if (empty($upline)) return collect();

        $uplineId = (is_string($upline) || is_int($upline)) ? intval($upline) : $upline->id;

        $data = ($internal === true) ? $this->internalCollection : $this->externalCollection;

        return $data->where('upline', '=', $uplineId);
    }

    public function nameById(bool $internal, int $id)
    {
        $data = $this->getDataById($internal, $id);
        return $data ? $data->name : null;
    }

    public function codeById(bool $internal, int $id)
    {
        $data = $this->getDataById($internal, $id);
        return $data ? $data->code : null;
    }

    public function nameByCode(bool $internal, string $code)
    {
        $data = $this->getDataByCode($internal, $code);
        return $data ? $data->name : null;
    }

    public function getExternalManagerOptions()
    {
        return $this->externalCollection->whereIn('id', [USER_EXT_DIST, USER_EXT_AG])->pluck('name', 'id')->toArray();
    }

    public function upline(bool $internal, $data)
    {
        $fixData = $data;
        if (is_int($data)) $fixData = $this->getDataById($internal, $data);
        if (is_string($data)) $fixData = $this->getDataByCode($internal, $data);

        if (empty($fixData)) return null;
        if (empty($fixData->upline)) return null;

        return $this->getDataById($internal, $fixData->upline);
    }

    public function downlines(bool $internal, $uplineData)
    {
        return $this->getDataByUpline($internal, $uplineData);
    }

    public function basicDiagram(bool $internal): HtmlString
    {
        $nodes = ($internal === true) ? $this->internalCollection : $this->externalCollection;
        $htmlNode = $this->setBasicNodeDiagram($nodes, $internal);

        return new HtmlString($htmlNode);
    }

    // private method untuk render node struktur
    private function setBasicNodeDiagram(Collection $nodes, bool $internal, $node = null): string
    {
        $htmlNode = is_null($node) ? '<ul>%s</ul>' : '%s';
        if (empty($node)) $node = $nodes->first();

        if (!empty($node)) {
            $html = $this->setNode([
                'elements' => [
                    [
                        'htmlText' => $node->name ?? '&nbsp;',
                        'cssClass' => 'text-primary fw-bold border-bottom'
                    ],
                    [
                        'htmlText' => $node->code ?? '&nbsp;',
                        'cssClass' => 'text-info'
                    ]
                ],
            ]);

            $html = "<li>{$html}%s</li>";
            $htmlchildren = '';

            $children = $nodes->where('upline', '=', $node->id);

            if ($children->isNotEmpty()) {
                foreach ($children as $value) {
                    $htmlchildren .= $this->setBasicNodeDiagram($nodes, $internal, $value);
                }
                $htmlchildren = sprintf('<ul>%s</ul>', $htmlchildren);
            }
            return sprintf($htmlNode, sprintf($html, $htmlchildren));
        }

        return '';
    }

    public function setNode($items = [])
    {
        $elmNode = '<div class="box-node small fs-auto text-nowrap">%s</div>';

        $elms = '';

        $logo = Arr::get($items, 'logo', []);
        if (!empty($logo)) {
            $image = Arr::get($logo, 'image');
            $cssClass = Arr::get($logo, 'cssClass');
            $styles = Arr::get($logo, 'cssStyle', []);
            $elms .= $this->setNodeLogo($image, $cssClass, $styles);
        }

        $infos = Arr::get($items, 'elements', []);
        if (count($infos) > 0) {
            foreach ($infos as $info) {
                $infoText = Arr::get($info, 'htmlText');
                $cssClass = Arr::get($info, 'cssClass');
                $styles = Arr::get($info, 'cssStyle', []);
                $elms .= $this->setElementNode($infoText, $cssClass, $styles);
            }
        }

        return sprintf($elmNode, $elms);
    }

    public function setNodeLogo(string $logo, string $cssClass = null, array $styles = null)
    {
        $style = '';
        if (!empty($styles)) {
            $style = implode('', array_map(function ($k, $v) {
                return "{$k}:{$v};";
            }, array_keys($styles), array_values($styles)));
        }

        return sprintf('<img src="%s" alt="Logo" class="%s" style="%s"/>', $logo, $cssClass ?? '', $style);
    }

    public function setElementNode(string $htmlText, string $cssClass = null, array $styles = null)
    {
        $style = '';
        if (!empty($styles)) {
            $style = implode('', array_map(function ($k, $v) {
                return "{$k}:{$v};";
            }, array_keys($styles), array_values($styles)));
        }

        return sprintf('<div class="%s" style="%s">%s</div>', $cssClass ?? '', $style, $htmlText);
    }
}
