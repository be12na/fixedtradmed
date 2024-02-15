<?php

namespace App\Reports\Excel;

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

class SummaryDetailBonus implements FromArray, ShouldAutoSize, WithStyles
{
    use Exportable;

    private Collection $rows;
    private Carbon $startDate;
    private Carbon $endDate;

    private $cellsMerge = [];
    private $rowBoldStyle = [];
    private $maxColumnCell = 'D';
    private $endHeaderNumber = 1;
    private $startRowHeader = 4;
    private $maxRowInSheet = 4;
    private $totalRoyalty = 0;
    private $totalOverride = 0;
    private $totalTeam = 0;
    private $totalSale = 0;
    private $totalBonus = 0;

    private $titleHeader = '';

    public function __construct(Collection $rows, Carbon $startDate, Carbon $endDate, string $titleHeader = null)
    {
        $this->rows = $rows;
        list($this->startDate, $this->endDate) = var1LowestEqualVar2($startDate->format('Y-m-d'), $endDate->format('Y-m-d'), [$startDate, $endDate]);

        $this->titleHeader = $titleHeader;
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
            [$this->titleHeader],
            [''],
            [$tanggal],
        ];

        $maxColumn = $this->maxColumnCell;
        $rowNumber = 3;

        $this->cellsMerge[] = "A1:{$maxColumn}1";
        $this->cellsMerge[] = "A{$rowNumber}:{$maxColumn}{$rowNumber}";

        $this->endHeaderNumber = $rowNumber;

        $result[] = [''];
        $rowNumber += 1;

        $this->setArrayData($result, $rowNumber);
        $this->maxRowInSheet = $rowNumber;

        $result[] = [null, 'Total', null, $this->totalBonus];
        $this->cellsMerge[] = "B{$rowNumber}:C{$rowNumber}";

        return $result;
    }

    private function setArrayData(array &$result, int &$rowNumber): void
    {
        $rowNumber += 1;
        $this->startRowHeader = $rowNumber;

        $nextRow = $rowNumber + 1;

        $result[] = ['No', 'Posisi', 'Anggota', 'Total Bonus'];

        $rowNumber = $nextRow;

        $noUrut = 1;
        $curPositionId = -1;

        foreach ($this->rows->sortBy('position_id')->groupBy('position_id') as $positionId => $positions) {
            $positionName = $positions->first()->position_name;

            foreach ($positions as $position) {
                $result[] = [
                    ($curPositionId != $positionId) ? $noUrut : null,
                    ($curPositionId != $positionId) ? $positionName : null,
                    $position->name,
                    ($totalBonus = intval($position->total_user)) ?: '0',
                ];

                $this->totalBonus += $totalBonus;

                $rowNumber += 1;
                $curPositionId = $positionId;
            }

            $noUrut += 1;
        }
    }
}
