<?php

namespace App\Exports;

use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemPriceExportSheet implements FromView, WithTitle, WithColumnWidths
{

    private $projectId, $project;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view() : View
    {

        $customItemPriceGroups = CustomItemPriceGroup::where('project_id', $this->projectId)->get();

        return view('exports.rab.item-price', [
            'customItemPricesGroups' => $customItemPriceGroups,
            'project' => $this->project,
        ]);
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
