<?php

namespace App\Reports\Point;

use App\Models\MitraPoint;
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

class ExcelListPoint implements FromArray, ShouldAutoSize, WithStyles
{
    use Exportable;

    private Collection $rows;
    private bool $isShoppingPoint;
    private Carbon $startDate;
    private Carbon $endDate;
    private string $titleHeader;
    private int $titleFontSize = 14;
    private int $defaultFontSize = 11;

    private array $values = [];

    public function __construct(Collection $rows, bool $isShoppingPoint, Carbon $startDate, Carbon $endDate, string $titleHeader)
    {
        $this->rows = $rows;
        $this->isShoppingPoint = $isShoppingPoint;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->titleHeader = $titleHeader;

        $this->setValuesAndStyles();
    }

    private function setValuesAndStyles(): void
    {
        $tanggal = formatFullDate($this->startDate);

        if ($this->startDate->format('Y-m-d') != $this->endDate->format('Y-m-d')) {
            $tanggal .= ' s/d ' . formatFullDate($this->endDate);
        }

        $lastColumn = $this->isShoppingPoint ? 'G' : 'F';

        $this->values = [
            'values' => [
                [$this->titleHeader],
                [$tanggal],
                [''],
            ],
            'styles' => [
                [
                    'range' => "A1:{$lastColumn}1",
                    'style' => [
                        'font' => [
                            'size' => $this->titleFontSize,
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ],
                ],
                [
                    'range' => "A2:{$lastColumn}2",
                    'style' => [
                        'font' => [
                            'size' => $this->defaultFontSize,
                            'bold' => true,
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                        ],
                    ],
                ],
                [
                    'range' => "A3:{$lastColumn}3",
                    'style' => [
                        'font' => [
                            'size' => $this->defaultFontSize,
                        ],
                    ],
                ],
            ],
            'merges' => [
                "A1:{$lastColumn}1",
                "A2:{$lastColumn}2",
                "A3:{$lastColumn}3",
            ],
        ];

        $columns = ['No', 'Tanggal', 'Member'];

        if (!$this->isShoppingPoint) {
            $columns[] = 'Sumber';
        }

        $columns[] = 'No. Transaksi';

        if ($this->isShoppingPoint) {
            $columns[] = 'Produk';
            $columns[] = 'Jumlah';
        }

        $columns[] = 'Poin';

        $this->values['values'][] = $columns;
        $this->values['styles'][] = [
            'range' => "A4:{$lastColumn}4",
            'style' => [
                'font' => [
                    'size' => $this->defaultFontSize,
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];

        $number = 1;
        $rowNumber = 5;

        $defaultStyle = [
            'font' => [
                'size' => $this->defaultFontSize,
            ],
        ];

        $totalPoint = 0;

        if ($this->rows->count() > 0) {
            foreach ($this->rows as $mitraPoint) {
                $user = $mitraPoint->user;

                $rowValues = [
                    $number,
                    formatFullDate($mitraPoint->point_date),
                    "{$user->name} ({$user->username})",
                ];

                if (!$this->isShoppingPoint) {
                    $fromUser = $mitraPoint->fromUser;
                    $rowValues[] = "{$fromUser->name} ({$fromUser->username})";
                }

                $rowValues[] = ($this->isShoppingPoint ? $mitraPoint->purchase : $mitraPoint->userPackage)->code;

                if ($this->isShoppingPoint) {
                    $product = $mitraPoint->purchaseProduct->product;
                    $rowValues[] = $product->name;
                    $rowValues[] = intval($mitraPoint->product_qty);
                }

                $rowValues[] = intval($mitraPoint->point);

                $rangeStyle = "A{$rowNumber}:{$lastColumn}{$rowNumber}";
                $this->values['values'][] = $rowValues;
                $this->values['styles'][] = [
                    'range' => $rangeStyle,
                    'style' => $defaultStyle,
                ];

                $totalPoint += $mitraPoint->point;
                $number++;
                $rowNumber++;
            }

            $footerValues = [
                'Total Poin',
                null,
                null,
            ];

            if (!$this->isShoppingPoint) {
                $footerValues[] = null;
            }

            $footerValues[] = null;

            if ($this->isShoppingPoint) {
                $footerValues[] = null;
                $footerValues[] = null;
            }

            $footerValues[] = intval($totalPoint);

            $rangeStyle = "A{$rowNumber}:{$lastColumn}{$rowNumber}";
            $lastMerge = $this->isShoppingPoint ? 'F' : 'E';

            $this->values['values'][] = $footerValues;
            $this->values['styles'][] = [
                'range' => $rangeStyle,
                'style' => [
                    'font' => [
                        'size' => $this->defaultFontSize,
                        'bold' => true,
                    ],
                ],
            ];

            $this->values['styles'][] = [
                'range' => "A{$rowNumber}:{$lastMerge}{$rowNumber}",
                'style' => [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ],
            ];

            $this->values['merges'][] = "A{$rowNumber}:{$lastMerge}{$rowNumber}";
        }
    }

    public function styles(Worksheet $sheet)
    {
        if (array_key_exists('styles', $this->values)) {
            foreach ($this->values['styles'] as $style) {
                $sheet->getStyle($style['range'])->applyFromArray($style['style']);
            }
        }

        if (array_key_exists('merges', $this->values)) {
            foreach ($this->values['merges'] as $merge) {
                $sheet->mergeCells($merge);
            }
        }
    }

    public function array(): array
    {
        $result = [];

        foreach ($this->values['values'] as $values) {
            if (!is_null($values) && !empty($values) && is_array($values)) $result[] = $values;
        }

        return $result;
    }
}
