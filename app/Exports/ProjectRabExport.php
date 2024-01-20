<?php

namespace App\Exports;

use App\Models\Project;
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

        $project = Project::find($this->projectId);

        if ($project->implementation_duration) {
            return [
                new RabSummaryExportSheet($this->projectId),
                new ImplementationScheduleExport($this->projectId),
                new AhsExportSheet($this->projectId),
                new AhsRekapExportSheet($this->projectId),
                new ItemPriceExportSheet($this->projectId),
                new AhpExportSheet($this->projectId),
            ];
        } else {
            return [
                new RabSummaryExportSheet($this->projectId),
                new AhsExportSheet($this->projectId),
                new AhsRekapExportSheet($this->projectId),
                new ItemPriceExportSheet($this->projectId),
                new AhpExportSheet($this->projectId),
            ];
        }
    }
}
