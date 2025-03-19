<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AhpExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths, WithStyles
{

    private $projectId;
    private $project;
    private $company;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = $project = Project::find($this->projectId);
        $this->company = Auth::user()->company;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view() : View
    {
        $customAhps = $this->project->customAhp->map(function($data) {
            return $this->countAhpItem($data);
        });

        return view('exports.rab.ahp', [
            'ahps' => $customAhps,
            'project' => $this->project,
            'company' => $this->company,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 75,
            'C' => 15,
            'D' => 21,
            'E' => 20,
            'F' => 75
        ];
    }

    public function styles(Worksheet $sheet)
    {

        $customAhpCount = $this->project->customAhp->count();
        $startingIndex = 12;
        $currentAIndex = $startingIndex;

        // Kop Surat
        $sheet->getStyle('F2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('153346');
        $sheet->getStyle('F2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A4:F4')->applyFromArray([
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE
                ]
            ]
        ]);


        for ($i = 0; $i < $customAhpCount; $i++) {
            $sheet->getStyle('A' . ($currentAIndex - 1) . ':B' . ($currentAIndex - 1))->applyFromArray(['borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]]);

            $sheet->getStyle('A' . $currentAIndex . ':F' . ($currentAIndex + 30))->applyFromArray(['borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000'],
                ],
            ]]);

            $headerStyle = $sheet->getStyle('A' . ($currentAIndex - 2) . ':F' . ($currentAIndex - 2));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');                

            $headerStyle = $sheet->getStyle('A' . ($currentAIndex - 1) . ':B' . ($currentAIndex - 1));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('D2E5F1');                

            $headerStyle = $sheet->getStyle('C' . ($currentAIndex - 1) . ':F' . ($currentAIndex - 1));
            $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');                

            $headerStyle = $sheet->getStyle('A' . $currentAIndex . ':F' . $currentAIndex);

            $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($currentAIndex)->setRowHeight(53);

            $headerStyle = $sheet->getStyle('A' . ($currentAIndex - 1) . ':F' . ($currentAIndex - 1));
            $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getRowDimension($currentAIndex - 1)->setRowHeight(32);
            // $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('153346');

            $currentAIndex += 34;
        }

        $sheet->getStyle('B10')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F' . ($currentAIndex) . ':F' . ($currentAIndex + 13))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    public function title() : string {
        return 'Analisa Harga Peralatan';
    }
}
