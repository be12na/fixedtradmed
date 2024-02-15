<?php

namespace App\Http\Controllers\Main;

use App\Helpers\AppStructure;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class StructureController extends Controller
{
    private AppStructure $appStructure;

    public function __construct()
    {
        $this->appStructure = app('appStructure');
    }

    public function basicDiagram(Request $request)
    {
        $isInternal = true;
        $diagram = $this->appStructure->basicDiagram($isInternal);

        return view('main.structures.diagram', [
            'diagram' => $diagram,
            'windowTitle' => 'Struktur Dasar',
            'breadcrumbs' => ['Struktur', 'Dasar']
        ]);
    }

    public function tableTreeview(Request $request)
    {
        if ($request->ajax()) {
            $parentId = $request->get('parent_id', 0);
            $rootId = $request->get('root_id', 0);
            $tableRows = '';

            $rootUser = User::byId($rootId)->first();
            $parent = User::byId($parentId)->first();
            $children = $parent->downlines;
            $ancestors = $parent->structure->ancestorsWithSelf()->get();

            foreach ($children as $child) {
                $tableRows .= $this->setTableTreeRow($child, $rootUser, $ancestors);
            }

            return response(new HtmlString($tableRows), 200);
        }

        $topUsers = User::byMemberGroup()->byInternalPosition(USER_INT_GM)->with('structure')->get();
        $tableRows = '';

        foreach ($topUsers as $topUser) {
            $tableRows .= $this->setTableTreeRow($topUser);
        }

        return view('main.structures.table-treeview', [
            'rows' => new HtmlString($tableRows),
            'windowTitle' => 'Tabel Struktural',
            'breadcrumbs' => ['Struktur', 'Table']
        ]);
    }

    private function setTableTreeRow(User $treeUser, User $rootUser = null, Collection $ancestors = null)
    {
        if (is_null($rootUser)) $rootUser = $treeUser;
        $structure = $treeUser->structure;
        $rootStructure = $rootUser->structure;

        if (is_null($ancestors)) $ancestors = collect();

        $row = "<tr id=\"{$treeUser->id}\" %s>%s</tr>";
        $cell = '<td class="%s" style="%s">%s</td>';

        $structureLevel = $rootStructure->descendantsWithSelf()->where('user_id', '=', $treeUser->id)->first();
        $level = $structureLevel->depth;

        $treeNode = "<div class=\"tree-node-container\">%s</div>";
        $iconNode = "<span class=\"icon-node fa-solid\"></span>";
        $nodeText = '<div class="node-text">' . $treeUser->name . '</div>';
        $hasChildren = $structure->hasChildren();
        $position = $treeUser->internal_position_name;
        $target = "row-{$treeUser->id}";
        $rowClasses = [];

        if ($ancestors->isNotEmpty()) {
            foreach ($ancestors as $ancestor) {
                $rowClasses[] = "row-{$ancestor->user_id}";
            }
        }

        if (!$hasChildren) {
            $rowClasses[] = 'no-children';
        }

        $rowClass = implode(' ', $rowClasses);
        $rowData = "data-children=\".{$target}\" data-root-id=\"{$rootUser->id}\" data-member-id=\"{$treeUser->id}\"";

        $cells = [
            sprintf($cell, 'cell-tree-node', "--tree-node-level:{$level}", sprintf($treeNode, $iconNode . $nodeText)),
            sprintf($cell, 'border-start', '', $position),
            sprintf($cell, 'text-end', '', formatCurrency($treeUser->totalOmzet(false), 0, true, false)),
            sprintf($cell, 'text-center', '', contentCheck(($treeUser->user_status == USER_STATUS_ACTIVE))),
        ];

        return sprintf($row, "class=\"{$rowClass}\" {$rowData}", implode('', $cells));
    }

    public function genealogyTree(Request $request)
    {
        $user = $request->user();

        $userTreeId = $request->userTreeId;
        $topUser = collect();
        if ($user->is_main_user) {
            if (empty($userTreeId)) {
                $topUser = User::byMemberGroup()->byInternalPosition(USER_INT_GM)->get();
            } else {
                $userTree = User::byId($userTreeId)->first();
                $topUser->push($userTree);
            }
        } else {
            if (!$user->is_member_customer_user && !$user->is_member_mitra_user) {
                $topUser->push($user);
            }
        }

        $app = $this->appStructure;

        $level = ($topUser->count() == 1) ? 0 : 1;

        $treeNodes = '';
        foreach ($topUser as $treeUser) {
            $treeNodes .= $this->setGenelogyTreeNode($app, $user, $treeUser, $level, 2);
        }

        $treeStructure = $user->is_main_user ? '<ul>%s</ul>' : '%s';

        if ($user->is_main_user && empty($userTreeId)) {
            $logoNode = $app->setNode([
                'logo' => [
                    'image' => asset('images/logo-main.png'),
                    'cssClass' => 'rounded mx-2 border-0',
                    'cssStyle' => ['height' => '3rem;'],
                ],

            ]);
            $treeNodes = sprintf("<li>{$logoNode}<ul>%s</ul></li>", $treeNodes);
        }

        $treeStructure = new HtmlString(sprintf($treeStructure, $treeNodes));

        return view('main.structures.tree-structure', [
            'treeStructure' => $treeStructure,
            'windowTitle' => 'Struktur Diagram',
            'breadcrumbs' => ['Struktur', 'Diagram']
        ]);
    }

    private function setGenelogyTreeNode(AppStructure $app, User $owner, User $user, $level = 0, $maxLevel = 2): string
    {
        if ($level > $maxLevel) return '';

        $asRoot = ($owner->id == $user->id);
        $htmlNode = $asRoot ? '<ul>%s</ul>' : '%s';

        $elements = [
            [
                'htmlText' => $user->name,
                'cssClass' => 'text-light fw-bold mx-1',
                'cssStyle' => ['background-color' => '#008000']
            ],
            [
                'htmlText' => '( <span class="text-decoration-underline">' . $user->internal_position_code . '</span> )',
                'cssClass' => 'text-primary bg-light mx-1'
            ],
        ];

        $elements[] = [
            'htmlText' => '<span class="me-2">Omzet :</span><span class="text-primary fw-bold">' . formatNumber($user->totalOmzet(false)) . '</span>',
            'cssClass' => 'mx-1'
        ];

        $elements[] = [
            'htmlText' => '<span class="me-2">Status :</span><span class="text-' . ($user->user_status == USER_STATUS_ACTIVE ? 'primary' : 'danger') . '">' . $user->status_name . '</span>',
            'cssClass' => 'mx-1'
        ];

        $linkFormat = '<a href="%s" class="btn btn-sm %s" title="%s">%s</a>';
        $downlines = $user->downlines;

        $rootLink = '';
        $uplineLink = '';
        if (!$asRoot && ($level == 0)) {
            $rootLink = sprintf($linkFormat, route('main.member.structure.tree'), 'btn-outline-primary mx-1', ($owner->is_main_user) ? 'Root' : 'Saya', '<i class="bi bi-house-fill"></i>');

            if ($user->upline) {
                $uplineLink = sprintf($linkFormat, route('main.member.structure.tree', ['userTreeId' => $user->upline->id]), 'btn-outline-success mx-1', 'Upline', '<i class="bi bi-box-arrow-up"></i>');
            }
        }

        $downlineLink = '';
        if ($downlines->isNotEmpty() && ($level > 0)) {
            $downlineLink = sprintf($linkFormat, route('main.member.structure.tree', ['userTreeId' => $user->id]), 'btn-outline-info mx-1', 'Downlines', '<i class="bi bi-diagram-3-fill"></i>');
        }

        $controlLink = $rootLink . $uplineLink . $downlineLink;

        if (!empty($controlLink)) {
            $elements[] = [
                'htmlText' => $controlLink,
                'cssClass' => 'd-flex justify-content-around border-top text-center py-1'
            ];
        }

        $elementsNode = [
            'elements' => $elements,
        ];

        $htmlchildren = '';
        $level += 1;
        if ($downlines->isNotEmpty() && ($level <= $maxLevel)) {
            foreach ($downlines as $downline) {
                $htmlchildren .= $this->setGenelogyTreeNode($app, $owner, $downline, $level, $maxLevel);
            }
            $htmlchildren = sprintf('<ul>%s</ul>', $htmlchildren);
        }

        $html = $app->setNode($elementsNode);
        $html = "<li>{$html}%s</li>";

        return sprintf($htmlNode, sprintf($html, $htmlchildren));
    }
}
