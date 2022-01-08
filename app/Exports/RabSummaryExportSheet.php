<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Rab;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class RabSummaryExportSheet extends CountableItemController implements FromView, WithTitle, WithColumnWidths
{

    private $projectId, $project;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view(): View
    {
        return view('exports.rab.rab', [
            'rabs' => $this->generateRab(),
            'project' => $this->project,
        ]);
    }

    private function generateRab()
    {

        $rabs = Rab::where('project_id', $this->projectId)
                ->with(['rabItemHeader.rabItem'])
                ->with('rabItem', function($q) {
                    $q->where('rab_item_header_id', NULL);
                    $q->with('customAhs');
                })
                ->get();

        $rabSubtotal = 0;

        foreach ($rabs as $key => $rab) {
            if ($rab->rabItem || ($rab->rabItemHeader && $rab->rabItemHeader->rabItem)) {
                foreach ($rab->rabItem as $key2 => $rabItem) {
                    if ($rabItem->customAhs) {
                        $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                        $countedAhs->price = $countedAhs->subtotal;
                        $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                        $rabSubtotal += $countedAhs->subtotal;
                    } else {
                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabs[$key]->rabItem[$key2] = $rabItem;
                        $rabSubtotal += $rabItem->subtotal;
                    }
                }

                foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                    foreach ($rabItemHeader->rabItem as $rabItem) {
                        if ($rabItem->customAhs) {
                            $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                            $countedAhs->price = $countedAhs->subtotal;
                            $countedAhs->subtotal = $countedAhs->subtotal * ($rabItem->volume ?? 0);
                            $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                            $rabSubtotal += $countedAhs->subtotal;
                        } else {
                            $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                            $rabSubtotal += $rabItem->subtotal;
                        }
                    }
                }
            } else {
                $rabSubtotal += 0;
            }

            $rabs[$key]->subtotal = $rabSubtotal;
            $rabSubtotal = 0;
        }

        return $rabs;
    }

    public function columnWidths(): array
    {
        return [
            'B' => 40,
            'C' => 17,
            'D' => 17,
            'E' => 17,
            'F' => 45,
            'G' => 23,
        ];
    }

    public function title() : string {
        return 'RAB';
    }
}
