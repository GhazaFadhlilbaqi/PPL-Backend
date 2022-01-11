<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Project;
use App\Models\CustomAhs;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AhsExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId, $project, $customAhsCount;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view(): View
    {

        $arrangedCustomAhs = $this->getArrangedCustomAhs();
        $this->customAhsCount = $arrangedCustomAhs['customAhsCount'];

        return view('exports.rab.ahs', [
            'ahs' => $arrangedCustomAhs['customAhs'],
            'project' => $this->project,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 75,
            'F' => 25,
            'G' => 25,
        ];
    }

    private function getArrangedCustomAhs()
    {
        $customAhs = CustomAhs::where('project_id', $this->projectId)->with(['customAhsItem' => function($q) {
            $q->with(['unit', 'customAhsItemable']);
        }])->get();

        $arrangedCustomAhs = [];
        $customAhsCount = [];

        foreach ($customAhs as $key => $cAhs) {

            foreach ($cAhs->customAhsItem as $cAhsItem) {
                $arrangedCustomAhs[$cAhsItem->section][] = $cAhsItem;
            }

            $customAhs[$key]['item_arranged'] = $arrangedCustomAhs;
            $arrangedCustomAhs = [];
            $customAhs[$key] = $this->countCustomAhsSubtotal($cAhs, $this->project->province->id);
            $customAhsCount[$key] = $cAhs->customAhsItem->count() + 12;
        }

        return [
            'customAhs' => $customAhs,
            'customAhsCount' => $customAhsCount
        ];
    }

    public function styles(Worksheet $sheet)
    {

        $currIndexPointer = 6;

        foreach ($this->customAhsCount as $customAhsCount) {
            $sheet->getStyle('A' . ($currIndexPointer + 1) . ':G' . ($currIndexPointer + $customAhsCount))->applyFromArray(['borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]]);

            $headerStyle = $sheet->getStyle('A' . ($currIndexPointer + 1) . ':G' . ($currIndexPointer + 1));

            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');
            $headerStyle->getFont()->getColor()->setRGB('FFFFFF');

            $currIndexPointer = $currIndexPointer + $customAhsCount + 2;
        }
    }

    public function title(): string
    {
        return 'AHSP';
    }
}
