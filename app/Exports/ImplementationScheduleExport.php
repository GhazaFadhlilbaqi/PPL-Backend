<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Rab;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithCharts;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Legend as ChartLegend;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ImplementationScheduleExport extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles, WithCharts
{
    
    private $projectId, $project, $company = null;
    private $rabStyleArr = [];
    private $globalStartingIndex = 13, $finalPointerLocation = 0;
    private $rabs = null;

    const RAB_HEADER = 'rabHeader';
    const RAB_ITEM_HEADER = 'rabItemHeader';
    const RAB_ITEM_CONTENT = 'rabItemContent';

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
        $this->company = Auth::user()->company;
        $this->rabs = $this->generateRab();
    }

    public function view(): View
    {
        return view('exports.rab.implementation-schedule', [
            'rabs' => $this->rabs,
            'project' => $this->project,
            'company' => $this->company
        ]);
    }

    public function charts()
    {

        $dataSeriesValueStartAt = $this->finalPointerLocation + 3;

        $dataSeriesLabels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Jadwal Pelaksanaan\'!$E$17', null, 1)];
        $xAxisTickValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, '\'Jadwal Pelaksanaan\'!$G$12:$' . $this->getNameFromNumber(6 + $this->project->implementation_duration) . '$12', null, $this->project->implementation_duration ?? 0)];
        $dataSeriesValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, '\'Jadwal Pelaksanaan\'!$G$' . $dataSeriesValueStartAt . ':$' . $this->getNameFromNumber(6 + $this->project->implementation_duration) .'$' . $dataSeriesValueStartAt, null, 4)];

        $series = new DataSeries(
            DataSeries::TYPE_LINECHART, // plotType
            null, // plotGrouping, was DataSeries::GROUPING_STACKED, not a usual choice for line chart
            range(0, count($dataSeriesValues) - 1), // plotOrder
            [], // plotLabel
            $xAxisTickValues, // plotCategory
            $dataSeriesValues        // plotValues
        );

        $plotArea = new PlotArea(null, [$series]);
        $legend = new ChartLegend(ChartLegend::POSITION_TOPRIGHT, null, false);

        $title = new Title('Kurva S');
        $yAxisLabel = new Title('Bobot Kumulatif');

        $chart = new Chart(
            'Kurva S Chart',
            $title,
            null,
            $plotArea,
            true,
            DataSeries::EMPTY_AS_GAP, // displayBlanksAs
            null, // xAxisLabel
            null,  // yAxisLabel
        );

        $chart->setTopLeftPosition($this->getNameFromNumber(8 + ($this->project->implementation_duration ?? 0)) . '12');
        $chart->setBottomRightPosition($this->getNameFromNumber(8 + ($this->project->implementation_duration ?? 0) + ($this->project->implementation_duration ?? 0)) . '25');

        return $chart;
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
            'E' => 17,
            'F' => 17,
        ];
    }

    public function styles(Worksheet $sheet)
    {

        $endColumn = $this->getNameFromNumber($this->project->implementation_duration + 6);

        foreach ($this->rabStyleArr as $styleArr) {

            if ($styleArr['type'] == self::RAB_HEADER || $styleArr['type'] == self::RAB_ITEM_HEADER) {
                // Styling for rab item header or rab header
                // Set background color
                $headerStyle = $sheet->getStyle('A' . $styleArr['pointer'] . ':' . $endColumn . $styleArr['pointer']);
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB( $styleArr['type'] == self::RAB_HEADER ? '153346' : '465059');
                $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
            } else {
                // Styling for rab item
                // $sheet->getStyle('E' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }


            if ($styleArr['type'] == self::RAB_ITEM_HEADER) {
                $sheet->getStyle('A' . $styleArr['pointer'])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // Centerize header
        $sheet->getStyle(('A' . ($this->globalStartingIndex - 1)) . (':' . $endColumn . ($this->globalStartingIndex - 1)))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Kop Surat
        $sheet->getStyle($endColumn . '2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('153346');
        $sheet->getStyle($endColumn . '2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($endColumn . '3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A4:' . $endColumn . '4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE
                ]
            ]
        ]);

        // Pekerjaan Detail
        $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('A' . ($this->globalStartingIndex - 1) . ':' . $endColumn . $this->finalPointerLocation + 1)->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        $sheet->getStyle('E' . $this->finalPointerLocation + 1 . ':' . $endColumn . $this->finalPointerLocation + 3)->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        // return dd($this->rabStyleArr);
    }

    public function title() : string {
        return 'Jadwal Pelaksanaan';
    }

    private function getNameFromNumber($num) {
        $numeric = ($num - 1) % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval(($num - 1) / 26);
        if ($num2 > 0) {
            return $this->getNameFromNumber($num2) . $letter;
        } else {
            return $letter;
        }
    }
}
