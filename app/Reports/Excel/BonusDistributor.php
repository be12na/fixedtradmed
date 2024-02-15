<?php

namespace App\Reports\Excel;

use App\Models\Branch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BonusDistributor implements FromArray, ShouldAutoSize, WithStyles
{
    use Exportable;

    private Collection $rows;
    private Carbon $startDate;
    private Carbon $endDate;
    private Branch $branch;
    private User $manager;

    private bool $hasBranch;
    private bool $hasManager;

    private $cellsMerge = [];
    private $maxColumnCell = 'E';
    private $endHeaderNumber = 1;
    private $startRowHeader = 4;
    private $maxRowInSheet = 4;
    private $totalOmzet = 0;
    private $totalBonus = 0;

    private $title = '';

    public function __construct(Collection $rows, Carbon $startDate, Carbon $endDate, Branch $branch = null, User $manager = null, string $title = null)
    {
        $this->rows = $rows;
        list($this->startDate, $this->endDate) = var1LowestEqualVar2($startDate->format('Y-m-d'), $endDate->format('Y-m-d'), [$startDate, $endDate]);
        if ($this->hasBranch = !empty($branch)) {
            $this->branch = $branch;
        }
        if ($this->hasManager = !empty($manager)) {
            $this->manager = $manager;
        }

        $this->title = $title;
    }

    public function styles(Worksheet $sheet)
    {
        // set default all font size and border
        $sheet->getStyle('A1:' . $this->maxColumnCell . $this->maxRowInSheet)->applyFromArray([
            'font' => [
                'size' => 10,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => Color::COLOR_BLACK],
                ],
            ],
        ]);

        // set header font size and style, set alignment, remove border
        $headerRange = 'A1:' . $this->maxColumnCell . $this->endHeaderNumber;
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'size' => 12,
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'color' => ['rgb' => Color::COLOR_WHITE],
                ],
            ],
        ]);

        // set space border left and right
        $spaceRange = 'A' . ($this->startRowHeader - 1) . ':' . $this->maxColumnCell . ($this->startRowHeader - 1);
        $sheet->getStyle($spaceRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'color' => ['rgb' => Color::COLOR_WHITE],
                ],
                'bottom' => [
                    'color' => ['rgb' => Color::COLOR_BLACK],
                ],
            ],
        ]);

        // set column header font style
        $columnHeaderRange = 'A' . $this->startRowHeader . ':' . $this->maxColumnCell . $this->startRowHeader;
        $sheet->getStyle($columnHeaderRange)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // set summary font style
        $rangeSummary = 'A' . $this->maxRowInSheet . ':' . $this->maxColumnCell . $this->maxRowInSheet;
        $sheet->getStyle($rangeSummary)->getFont()->setBold(true);

        // set merge cells
        if (!empty($this->cellsMerge)) {
            foreach ($this->cellsMerge as $range) {
                $sheet->mergeCells($range);
            }
        }
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $tanggal = formatFullDate($this->startDate);

        if ($this->startDate->format('Y-m-d') != $this->endDate->format('Y-m-d')) {
            $tanggal .= ' s/d ' . formatFullDate($this->endDate);
        }

        $result = [
            [$this->title],
            [''],
            [$tanggal],
        ];

        $maxColumn = 'E';
        $summary = [null, 'Total'];

        if ($this->hasBranch || $this->hasManager) {
            $maxColumn = 'D';
        }

        $this->maxColumnCell = $maxColumn;

        $rowNumber = 3;
        $this->cellsMerge[] = "A1:{$maxColumn}1";
        $this->cellsMerge[] = "A{$rowNumber}:{$maxColumn}{$rowNumber}";

        if ($this->hasManager) {
            $result[] = ['Manager: ' . $this->manager->name];
            $rowNumber += 1;
            $this->cellsMerge[] = "A{$rowNumber}:{$maxColumn}{$rowNumber}";
        }

        if ($this->hasBranch) {
            $result[] = ['Cabang: ' . $this->branch->name];
            $rowNumber += 1;
            $this->cellsMerge[] = "A{$rowNumber}:{$maxColumn}{$rowNumber}";
        }

        $this->endHeaderNumber = $rowNumber;

        $result[] = [''];
        $rowNumber += 1;

        $this->setArrayData($result, $rowNumber);
        $this->maxRowInSheet = $rowNumber;
        $summary[] = $this->totalOmzet ?: '0';
        $summary[] = $this->totalBonus ?: '0';

        $result[] = $summary;

        return $result;
    }

    private function setArrayData(array &$result, int &$rowNumber): void
    {
        $rowNumber += 1;

        $this->startRowHeader = $rowNumber;

        $header1 = ['No']; // nomor urut

        if ($this->hasBranch && $this->hasManager) {
            $header1[] = null;
        } else {
            if (!$this->hasBranch && !$this->hasManager) {
                $header1[] = 'Manager'; // kolom manager
                $header1[] = 'Cabang'; // kolom cabang
            } else {
                if (!$this->hasManager) {
                    $header1[] = 'Manager'; // kolom manager
                }

                if (!$this->hasBranch) {
                    $header1[] = 'Cabang'; // kolom cabang
                }
            }
        }

        $header1[] = 'Total Omzet';
        $header1[] = 'Total Bonus';

        $result[] = $header1;

        $rowNumber += 1;

        $noUrut = 1;

        foreach ($this->rows as $row) {
            $totalOmzet = intval($row->total_omzet);
            $totalBonus = intval($row->total_bonus);

            $this->totalOmzet += $totalOmzet;
            $this->totalBonus += $totalBonus;

            $rowResult = [$noUrut++];

            if ($this->hasManager && $this->hasBranch) {
                $rowResult[] = null;
            }

            if (!$this->hasManager) {
                $rowResult[] = $row->manager_name . ' (' . $row->username . ')';
            }

            if (!$this->hasBranch) {
                $rowResult[] = $row->branch_name;
            }

            $rowResult[] = $totalOmzet ?: '0';
            $rowResult[] = $totalBonus ?: '0';

            $result[] = $rowResult;
            $rowNumber += 1;
        }
    }
}
