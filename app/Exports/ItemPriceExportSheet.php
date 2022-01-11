<?php

namespace App\Exports;

use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemPriceExportSheet implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId, $project, $customItemPriceGroupsCount;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view() : View
    {

        $customItemPriceGroups = CustomItemPriceGroup::where('project_id', $this->projectId)->get();

        $this->customItemPriceGroupsCount = $customItemPriceGroups->map(function($customItemPriceGroup) {
            return $customItemPriceGroup->customItemPrice->count();
        })->sum() + ($customItemPriceGroups->count() * 2);

        return view('exports.rab.item-price', [
            'customItemPricesGroups' => $customItemPriceGroups,
            'project' => $this->project,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A' . 6 . ':E' . (6 + $this->customItemPriceGroupsCount))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]
        ]);

        $headerStyle = $sheet->getStyle('A6:E6');
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
    }

    public function title() : string
    {
        return 'Harga Satuan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 25,
            'C' => 10,
            'D' => 10,
            'E' => 15
        ];
    }
}
