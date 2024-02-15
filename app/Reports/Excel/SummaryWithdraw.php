<?php

namespace App\Reports\Excel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummaryWithdraw implements FromArray, ShouldAutoSize, WithStyles
{
    use Exportable;

    private Collection $rows;
    private Carbon $startDate;
    private Carbon $endDate;

    private string $title = '';

    private $cellsMerge = [];
    private $rowSummary = [];
    private $maxColumnCell = 'J';
    private $endHeaderNumber = 1;
    private $startRowHeader = 4;
    private $maxRowInSheet = 4;

    private $totalBonus = 0;
    private $totalFee = 0;
    private $totalTransfer = 0;

    public function __construct(
        Collection $rows,
        Carbon $startDate,
        Carbon $endDate,
        string $title = null
    ) {
        $this->rows = $rows;
        list($this->startDate, $this->endDate) = var1LowestEqualVar2($startDate->format('Y-m-d'), $endDate->format('Y-m-d'), [$startDate, $endDate]);

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
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
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

            $totalCell = 'A' . $this->maxRowInSheet;
            $sheet->getStyle($totalCell)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);
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

        $maxColumn = $this->maxColumnCell;

        $result = [
            [$this->title],
            [''],
            [$tanggal],
        ];

        $rowNumber = 3;

        $this->cellsMerge[] = "A1:{$maxColumn}1";
        $this->cellsMerge[] = "A{$rowNumber}:{$maxColumn}{$rowNumber}";
        $this->endHeaderNumber = $rowNumber;

        $result[] = [''];
        $rowNumber += 1;

        $summary = ['Total', null, null, null, null, null];

        $this->setArrayData($result, $rowNumber);
        $this->maxRowInSheet = $rowNumber;
        $this->cellsMerge[] = "A{$rowNumber}:F{$rowNumber}";

        $summary[] = $this->totalBonus ?: '0';
        $summary[] = $this->totalFee ?: '0';
        $summary[] = $this->totalTransfer ?: '0';

        $result[] = $summary;

        return $result;
    }

    private function setArrayData(array &$result, int &$rowNumber): void
    {
        $rowNumber += 1;
        $this->startRowHeader = $rowNumber;

        $result[] = ['No', 'Tanggal', 'Kode', 'Jenis', 'Member', 'Bank', 'Bonus', 'Fee', 'Transfer', 'Status'];
        $rowNumber += 1;
        $noUrut = 1;

        foreach ($this->rows as $userWithdraw) {
            $bonus = $userWithdraw->total_bonus;
            $fee = $userWithdraw->fee;
            $transfer = $userWithdraw->total_transfer;

            $this->totalBonus += $bonus;
            $this->totalFee += $fee;
            $this->totalTransfer += $transfer;

            $user = $userWithdraw->user;

            $result[] = [
                $noUrut++,
                $userWithdraw->wd_date->translatedFormat('d M Y'),
                $userWithdraw->wd_code,
                $userWithdraw->bonus_type_name,
                "{$user->name}\r\n{$user->username} - {$user->phone}",
                "{$userWithdraw->bank_code}\r\n{$userWithdraw->bank_acc_no}\r\n{$userWithdraw->bank_acc_name}",
                $bonus ?: '0',
                $fee ?: '0',
                $transfer ?: '0',
                $userWithdraw->status_name
            ];

            $rowNumber += 1;
        }
    }
}
