<?php

namespace App\Exports;

use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemPriceExportSheet implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId, $project, $company, $customItemPriceGroupsCount;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
        $this->company = Auth::user()->company;
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
            'company' => $this->company,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A' . 11 . ':E' . (11 + $this->customItemPriceGroupsCount))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]
        ]);

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

        $headerStyle = $sheet->getStyle('A11:E11');
        $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');
        $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

        $sheet->getStyle('E' . (12 + $this->customItemPriceGroupsCount) . ':E' . (25 + $this->customItemPriceGroupsCount))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A11:E11')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
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
