<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Project;
use App\Models\CustomAhs;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class AhsExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths
{

    private $projectId, $project;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view(): View
    {
        return view('exports.rab.ahs', [
            'ahs' => $this->getArrangedCustomAhs(),
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

        foreach ($customAhs as $key => $cAhs) {

            foreach ($cAhs->customAhsItem as $cAhsItem) {
                $arrangedCustomAhs[$cAhsItem->section][] = $cAhsItem;
            }

            $customAhs[$key]['item_arranged'] = $arrangedCustomAhs;
            $arrangedCustomAhs = [];
            $customAhs[$key] = $this->countCustomAhsSubtotal($cAhs, $this->project->province->id);
        }

        return $customAhs;
    }

    public function title(): string
    {
        return 'AHSP';
    }
}
