<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Project;
use App\Models\CustomAhs;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AhsExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId, $project, $company, $customAhsCount;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
        $this->company = Auth::user()->company;
    }

    public function view(): View
    {

        $arrangedCustomAhs = $this->getArrangedCustomAhs();
        $this->customAhsCount = $arrangedCustomAhs['customAhsCount'];

        Log::info($arrangedCustomAhs['customAhs']);

        return view('exports.rab.ahs', [
            'ahs' => $arrangedCustomAhs['customAhs'],
            'project' => $this->project,
            'company' => $this->company,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
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

        $currIndexPointer = 11;

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

        foreach ($this->customAhsCount as $customAhsCount) {
            $sheet->getStyle('A' . ($currIndexPointer + 1) . ':G' . ($currIndexPointer + $customAhsCount))->applyFromArray(['borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]]);
            $sheet->getStyle('A' . ($currIndexPointer) . ':B' . ($currIndexPointer))->applyFromArray(['borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]]);

            $headerStyle = $sheet->getStyle('A' . ($currIndexPointer - 1) . ':G' . ($currIndexPointer - 1));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');

            $headerStyle = $sheet->getStyle('A' . ($currIndexPointer) . ':B' . ($currIndexPointer));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('D2E5F1');
            $headerStyle->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            $headerStyle = $sheet->getStyle('C' . ($currIndexPointer) . ':G' . ($currIndexPointer));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');

            $headerStyle = $sheet->getStyle('A' . ($currIndexPointer + 1) . ':G' . ($currIndexPointer + 1));
            $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($currIndexPointer + 1)->setRowHeight(56);
            $sheet->getRowDimension($currIndexPointer)->setRowHeight(21);


            $currIndexPointer = $currIndexPointer + $customAhsCount + 3;
        }

        $sheet->getStyle('B9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . ($currIndexPointer + 1) . ':G' . ($currIndexPointer + 11))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function title(): string
    {
        return 'AHSP';
    }
}
