<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Rab;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RabSummaryExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId, $project, $company = null;
    private $rabStyleArr = [];
    private $globalStartingIndex = 12, $finalPointerLocation = 0;

    const RAB_HEADER = 'rabHeader';
    const RAB_ITEM_HEADER = 'rabItemHeader';
    const RAB_ITEM_CONTENT = 'rabItemContent';

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
        $this->company = Auth::user()->company;
    }

    public function view(): View
    {
        $rabList = $this->generateRab();
        $subtotal_price = $rabList->sum(fn($rab) => $rab->subtotal);
        $tax_fee = round($subtotal_price * ($this->project->ppn/ 100));
        return view('exports.rab.rab', [
            'rabs' => $this->generateRab(),
            'project' => $this->project,
            'company' => $this->company,
            'tax' => $tax_fee,
            'price_after_tax' => $subtotal_price + $tax_fee
        ]);
    }

    private function generateRab()
    {

        $rabSubtotal = 0;
        $pointer = $this->globalStartingIndex;

        $rabs = Rab::where('project_id', $this->projectId)
            ->with(['rabItemHeader.rabItem'])
            ->with('rabItem', function ($q) {
                $q->where('rab_item_header_id', NULL);
                $q->with('customAhs');
            })
            ->get();

        foreach ($rabs as $key => $rab) {

            $this->rabStyleArr[] = [
                'pointer' => $pointer,
                'type' => self::RAB_HEADER
            ];
            $pointer++;

            $rabPerSectionCount = 0;

            if (!$rab->rabItem && !$rab->rabItemHeader && !$rab->rabItemHeader->rabItem) {
                $rabSubtotal += 0;
                continue;
            }

            // 1) Handle RAB item without header
            foreach ($rab->rabItem as $key2 => $rabItem) {
                $this->rabStyleArr[] = [
                    'pointer' => $pointer,
                    'type' => self::RAB_ITEM_CONTENT
                ];
                $pointer++;

                // 1.A) Handle RAB item with no AHS attached
                if ($rabItem->customAhs == null) {
                    $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                    $rabs[$key]->rabItem[$key2] = $rabItem;
                    $rabSubtotal += $rabItem->subtotal;
                    $rabPerSectionCount += $rabItem->subtotal;
                    continue;
                }

                // 1.B) Handle RAB item with AHS attached
                $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                $countedAhs->price = round($countedAhs->subtotal * (1 + ($this->project->profit_margin/100)));
                $rabItem->subtotal = $countedAhs->price;
                $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                $rabSubtotal += $countedAhs->price;
                $rabPerSectionCount += $countedAhs->price * $rabItem->volume;
            }

            // 2) Handle RAB item with header
            foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                $this->rabStyleArr[] = [
                    'pointer' => $pointer,
                    'type' => self::RAB_ITEM_HEADER
                ];
                $pointer++;

                foreach ($rabItemHeader->rabItem as $key4 => $rabItem) {
                    $this->rabStyleArr[] = [
                        'pointer' => $pointer,
                        'type' => self::RAB_ITEM_CONTENT
                    ];
                    $pointer++;

                    // 2.A) Handle RAB item with no AHS attached
                    if ($rabItem->customAhs == null) {
                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabSubtotal += $rabItem->subtotal;
                        $rabPerSectionCount += $rabItem->subtotal;
                        continue;
                    }

                    // 2.B) Handle RAB item with AHS attached
                    $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                    $countedAhs->price = round($countedAhs->subtotal * (1 + ($this->project->profit_margin/100)));
                    $rabItem->subtotal = $countedAhs->price;
                    $rabs[$key]->rabItemHeader[$key3]->rabItem[$key4]['custom_ahs'] = $countedAhs;
                    $rabSubtotal += $countedAhs->price;
                    $rabPerSectionCount += $countedAhs->price * $rabItem->volume;
                }
            }
            $rab->subtotal = $rabPerSectionCount;
        }
        
        $this->finalPointerLocation = $pointer - 1;

        return $rabs;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 40,
            'C' => 17,
            'D' => 15,
            'E' => 21,
            'F' => 29,
            'G' => 28,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $headerStyle = $sheet->getStyle('A' . 10 . ':G' . 10);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');
        foreach ($this->rabStyleArr as $styleArr) {

            if ($styleArr['type'] == self::RAB_HEADER || $styleArr['type'] == self::RAB_ITEM_HEADER) {
                // Styling for rab item header or rab header
                // Set background color
                $headerStyle = $sheet->getStyle('A' . $styleArr['pointer'] . ':G' . $styleArr['pointer']);
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB($styleArr['type'] == self::RAB_HEADER ? 'D2E5F1' : 'D7D7D7');
            } else {
                // Styling for rab item
                $sheet->getStyle('E' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }


            if ($styleArr['type'] == self::RAB_ITEM_HEADER) {
                $sheet->getStyle('A' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // Centerize header
        $sheet->getStyle(('A' . ($this->globalStartingIndex - 1)) . (':G' . ($this->globalStartingIndex - 1)))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($this->globalStartingIndex - 1)->setRowHeight(56);

        // Kop Surat
        $sheet->getStyle('G2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('153346');
        $sheet->getStyle('G2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('G3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A4:G4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE
                ]
            ]
        ]);

        // Pekerjaan Detail
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A' . ($this->globalStartingIndex - 1) . ':G' . $this->finalPointerLocation)->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        $sheet->getStyle('F' . ($this->finalPointerLocation + 2) . ':G' . ($this->finalPointerLocation + 5))->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        $sheet->getStyle('G' . (8 + $this->finalPointerLocation) . ':G' . (18 + $this->finalPointerLocation))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // return dd($this->rabStyleArr);
    }

    public function title(): string
    {
        return 'RAB';
    }
}
