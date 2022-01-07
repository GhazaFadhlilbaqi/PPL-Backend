<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Project;
use Illuminate\Contracts\View\View;
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

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = $project = Project::find($this->projectId);
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
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'B' => 75,
            'C' => 15,
            'D' => 15,
            'E' => 20,
            'F' => 75
        ];
    }

    public function styles(Worksheet $sheet)
    {

        $customAhpCount = $this->project->customAhp->count();
        $currentAIndex = 1;

        for ($i = 0; $i < $customAhpCount; $i++) {

            $sheet->getStyle('A' . $currentAIndex . ':F' . ($currentAIndex + 30))->applyFromArray(['borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['rgb' => '000'],
                ],
            ]]);

            $currentAIndex += 33;
        }
    }

    public function title() : string {
        return 'Analisa Harga Peralatan';
    }
}
