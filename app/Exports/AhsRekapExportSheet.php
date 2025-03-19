<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Rab;
use App\Models\Project;
use App\Models\User;
use Exception;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AhsRekapExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
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
        return view('exports.rab.ahs-rekap', [
            'rabs' => $this->generateRab(),
            'project' => $this->project,
            'company' => $this->company
        ]);
    }

    private function generateRab()
    {
        try {
            $rabSubtotal = 0;
            $pointer = $this->globalStartingIndex;

            $rabs = Rab::where('project_id', $this->projectId)
            ->with(['rabItemHeader.rabItem' => function($q) {
                $q->where('custom_ahs_id', '!=', null);
            }])
            ->with('rabItem', function($q) {
                $q->where('custom_ahs_id', '!=', null);
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

                        foreach ($rabItemHeader->rabItem as $key4 => $rabItem) {

                            if ($rabItem->customAhs) {

                                $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                                $countedAhs->price = $countedAhs->subtotal;
                                $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                                $rabItem->subtotal = $countedAhs->subtotal;
                                $rabs[$key]->rabItemHeader[$key3]->rabItem[$key4]['custom_ahs'] = $countedAhs;
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

            Log::info('Printing RABs');

            $this->finalPointerLocation = $pointer - 1;

            if ($rabs && count($rabs) > 0) {
                Log::info('Printing RABs');
                Log::info($rabs);
            } else {
                Log::error('No RABS');
            }

            return $rabs;
        } catch (Exception $e) {
            Log::info('Error happened');
            Log::error($e);
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 40,
            'C' => 17,
            'D' => 15,
            'E' => 25
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $headerStyle = $sheet->getStyle('A' . $this->globalStartingIndex - 2 . ':E' . $this->globalStartingIndex - 2);
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');
        foreach ($this->rabStyleArr as $styleArr) {

            if ($styleArr['type'] == self::RAB_HEADER || $styleArr['type'] == self::RAB_ITEM_HEADER) {
                // Styling for rab item header or rab header
                // Set background color
                $headerStyle = $sheet->getStyle('A' . $styleArr['pointer'] . ':E' . $styleArr['pointer']);
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB( $styleArr['type'] == self::RAB_HEADER ? 'D2E5F1' : 'D7D7D7');
            } else {
                // Styling for rab item
                $sheet->getStyle('D' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }


            if ($styleArr['type'] == self::RAB_ITEM_HEADER) {
                $sheet->getStyle('A' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // Centerize header
        $sheet->getStyle(('A' . ($this->globalStartingIndex - 1)) . (':E' . ($this->globalStartingIndex - 1)))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
        ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension($this->globalStartingIndex - 1)->setRowHeight(56);

        // Kop Surat
        $sheet->getStyle('E2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('153346');
        $sheet->getStyle('E2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A4:E4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE
                ]
            ]
        ]);

        // Pekerjaan Detail
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A' . ($this->globalStartingIndex - 1) . ':E' . $this->finalPointerLocation)->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        // $sheet->getStyle('F' . ($this->finalPointerLocation + 2) . ':G' . ($this->finalPointerLocation + 5))->applyFromArray(['borders' => [
        //     'allBorders' => [
        //         'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        //         'color' => ['rgb' => '000'],
        //     ],
        // ]]);

        $sheet->getStyle('E' . (2 + $this->finalPointerLocation) . ':E' . (18 + $this->finalPointerLocation))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function title() : string {
        return 'Rekap AHSP';
    }
}
