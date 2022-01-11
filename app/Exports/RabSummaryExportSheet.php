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
    private $rabStartingIdx = 8, $prevIdx = 0;
    private $rabStyleArr = [];

    const RAB_HEADER = 'rabHeader';
    const RAB_ITEM_HEADER = 'rabItemHeader';

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
        $this->company = User::find(1)->company;
    }

    public function view(): View
    {

        // return dd($this->generateRab());

        return view('exports.rab.rab', [
            'rabs' => $this->generateRab(),
            'project' => $this->project,
            'company' => $this->company
        ]);
    }

    private function generateRab()
    {

        $rabs = Rab::where('project_id', $this->projectId)
                ->with(['rabItemHeader.rabItem'])
                ->with('rabItem', function($q) {
                    $q->where('rab_item_header_id', NULL);
                    $q->with('customAhs');
                })
                ->get();

        $rabSubtotal = 0;

        foreach ($rabs as $key => $rab) {
            if ($rab->rabItem || ($rab->rabItemHeader && $rab->rabItemHeader->rabItem)) {
                foreach ($rab->rabItem as $key2 => $rabItem) {
                    if ($rabItem->customAhs) {
                        $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                        $countedAhs->price = $countedAhs->subtotal;
                        $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                        $rabSubtotal += $countedAhs->subtotal;
                    } else {
                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2] = $rabItem;
                        $rabSubtotal += $rabItem->subtotal;
                    }
                }

                $this->rabStyleArr[] = [
                    'index' => $this->rabStartingIdx + $this->prevIdx,
                    'type' => self::RAB_HEADER
                ];

                $prevRabItemCount = 0;
                $overallHeaderRabCount = 0;

                foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {

                    foreach ($rabItemHeader->rabItem as $rabItem) {
                        if ($rabItem->customAhs) {
                            $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                            $countedAhs->price = $countedAhs->subtotal;
                            $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                            $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                            $rabSubtotal += $countedAhs->subtotal;
                        } else {
                            $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                            $rabSubtotal += $rabItem->subtotal;
                        }
                    }

                    $this->rabStyleArr[] = [
                        'index' => $this->rabStartingIdx + $this->prevIdx + $rab->rabItem->count() + $prevRabItemCount + 1,
                        'type' => self::RAB_ITEM_HEADER
                    ];

                    $prevRabItemCount = $rabItemHeader->rabItem->count();
                    $overallHeaderRabCount += $prevRabItemCount + 1;
                }

                $this->prevIdx = $overallHeaderRabCount + $rab->rabItem->count();

            } else {
                $rabSubtotal += 0;
                $this->prevIdx = 0;
            }

            $rabs[$key]->subtotal = $rabSubtotal;
            $rabSubtotal = 0;
        }

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
            'F' => 45,
            'G' => 23,
        ];
    }

    public function styles(Worksheet $sheet)
    {

        foreach ($this->rabStyleArr as $styleArr) {
            $headerStyle = $sheet->getStyle('A' . $styleArr['index'] . ':G' . $styleArr['index']);
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB( $styleArr['type'] == self::RAB_HEADER ? '153346' : '465059');
            $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
        }

        $sheet->getStyle('A' . ($this->rabStartingIdx - 1) . ':G' . ($this->rabStartingIdx + $this->prevIdx))->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        $sheet->getStyle('F' . (($this->rabStartingIdx + $this->prevIdx) + 2) . ':G' . (($this->rabStartingIdx + $this->prevIdx) + 4))->applyFromArray(['borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => '000'],
            ],
        ]]);

        // return dd($this->rabStyleArr);
    }

    public function title() : string {
        return 'RAB';
    }
}
