<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Contracts\View\View;
use App\Models\Rab;
use App\Models\Project;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Auth;

class MaterialEstimatorExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
{
    
    private $projectId, $project, $company = null;
    private $rabStyleArr = [];
    private $globalStartingIndex = 13, $finalPointerLocation = 0;

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
        return view('exports.rab.material-estimator', [
            'rabs' => $this->generateRab(),
            'project' => $this->project,
            'company' => $this->company
        ]);
    }

    private function generateRab()
    {

        $rabSubtotal = 0;
        $pointer = $this->globalStartingIndex;

        $rabs = Rab::where('project_id', $this->projectId)
          ->with(['rabItemHeader.rabItem'])
          ->with('rabItem', function($q) {
              $q->where('rab_item_header_id', NULL);
              $q->with('customAhs');
          })
          ->get();

        foreach ($rabs as $key => $rab) {

            $this->rabStyleArr[] = [
                'pointer' => $pointer,
                'type' => self::RAB_HEADER
            ];

            $rabPerSectionCount = 0;

            if ($rab->rabItem || ($rab->rabItemHeader && $rab->rabItemHeader->rabItem)) {

                foreach ($rab->rabItem as $key2 => $rabItem) {

                    if ($rabItem->customAhs) {

                        $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                        $countedAhs->price = $countedAhs->subtotal;
                        $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                        $rabItem->subtotal = $countedAhs->subtotal;
                        $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                        $rabSubtotal += $countedAhs->subtotal;
                        $rabPerSectionCount += $countedAhs->subtotal;

                    } else {

                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2] = $rabItem;
                        $rabSubtotal += $rabItem->subtotal;
                        $rabPerSectionCount += $rabSubtotal;

                    }

                    $pointer++;

                    $this->rabStyleArr[] = [
                        'pointer' => $pointer,
                        'type' => self::RAB_ITEM_CONTENT
                    ];

                }

                foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {

                    $pointer++;

                    $this->rabStyleArr[] = [
                        'pointer' => $pointer,
                        'type' => self::RAB_ITEM_HEADER
                    ];

                    foreach ($rabItemHeader->rabItem as $rabItem) {

                        if ($rabItem->customAhs) {

                            $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                            $countedAhs->price = $countedAhs->subtotal;
                            $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                            $rabItem->subtotal = $countedAhs->subtotal;
                            $rabs[$key]->rabItem[$key3]['custom_ahs'] = $countedAhs;
                            $rabSubtotal += $countedAhs->subtotal;
                            $rabPerSectionCount += $countedAhs->subtotal;

                        } else {

                            $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                            $rabSubtotal += $rabItem->subtotal;
                            $rabPerSectionCount += $rabSubtotal;

                        }

                        $pointer++;

                        $this->rabStyleArr[] = [
                            'pointer' => $pointer,
                            'type' => self::RAB_ITEM_CONTENT
                        ];

                    }
                }

            } else {
                $rabSubtotal += 0;
            }

            $rab->subtotal = $rabPerSectionCount;

            $pointer++;
        }

        $this->finalPointerLocation = $pointer - 1;

        return $rabs;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 40,
            'C' => 17,
            'D' => 17,
            'E' => 21,
            'F' => 11,
            'G' => 22,
            'H' => 35,
        ];
    }

    public function styles(Worksheet $sheet)
    {

        foreach ($this->rabStyleArr as $styleArr) {

            if ($styleArr['type'] == self::RAB_HEADER || $styleArr['type'] == self::RAB_ITEM_HEADER) {
                // Styling for rab item header or rab header
                // Set background color
                // $headerStyle = $sheet->getStyle('A' . $styleArr['pointer'] . ':G' . $styleArr['pointer']);
                // $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB( $styleArr['type'] == self::RAB_HEADER ? '153346' : '465059');
                // $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
            } else {
                // Styling for rab item
                // $sheet->getStyle('E' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }


            if ($styleArr['type'] == self::RAB_ITEM_HEADER) {
                // $sheet->getStyle('A' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // Centerize header
        $sheet->getStyle(('A' . ($this->globalStartingIndex - 1)) . (':H' . ($this->globalStartingIndex - 1)))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Kop Surat
        $sheet->getStyle('H2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('153346');
        $sheet->getStyle('H2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A4:H4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE
                ]
            ]
        ]);

        // Pekerjaan Detail
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A' . ($this->globalStartingIndex - 1) . ':H' . $this->finalPointerLocation)->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        $sheet->getStyle('F' . ($this->finalPointerLocation + 2) . ':H' . ($this->finalPointerLocation + 5))->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        // $sheet->getStyle('G' . (8 + $this->finalPointerLocation) . ':H' . (18 + $this->finalPointerLocation))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // return dd($this->rabStyleArr);
    }

    public function title() : string {
        return 'Hitung Bahan';
    }
}
