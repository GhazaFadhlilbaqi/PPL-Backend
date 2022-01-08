<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectRabExport implements WithMultipleSheets
{

    use Exportable;

    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function sheets(): array {

        $sheets = [
            new RabSummaryExportSheet($this->projectId),
            new AhsExportSheet($this->projectId),
            new ItemPriceExportSheet($this->projectId),
            new AhpExportSheet($this->projectId),
        ];

        return $sheets;
    }
}
