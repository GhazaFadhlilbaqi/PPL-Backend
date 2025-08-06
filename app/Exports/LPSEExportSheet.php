<?php

namespace App\Exports;

use App\Http\Controllers\CountableItemController;
use App\Models\Rab;
use App\Models\Project;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Log;

class LPSEExportSheet extends CountableItemController implements FromView, WithColumnWidths
{

    private $projectId, $project, $company = null;
    private $rabStyleArr = [];
    private $globalStartingIndex = 0, $finalPointerLocation = 0;

    const RAB_HEADER = 'rabHeader';
    const RAB_ITEM_HEADER = 'rabItemHeader';
    const RAB_ITEM_CONTENT = 'rabItemContent';

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
        $this->project = Project::find($projectId);
    }

    public function view(): View
    {
        return view('exports.rab.lpse', [
            'rabs' => $this->generateRab(),
            'project' => $this->project
        ]);
    }

    private function generateRab()
    {
        $rabSubtotal = 0;
        $pointer = $this->globalStartingIndex;

        $rabs = Rab::where('project_id', $this->projectId)
            ->with(['rabItemHeader.rabItem'])
            ->with('rabItem', function ($q) {
                $q->where('rab_item_header_id', NULL);
                $q->with('customAhs');
            })
            ->get();

        foreach ($rabs as $key => $rab) {

            $this->rabStyleArr[] = [
                'pointer' => $pointer,
                'type' => self::RAB_HEADER
            ];
            $pointer++;

            $rabPerSectionCount = 0;

            if (!$rab->rabItem && !$rab->rabItemHeader && !$rab->rabItemHeader->rabItem) {
                $rabSubtotal += 0;
                continue;
            }

            // 1) Handle RAB item without header
            foreach ($rab->rabItem as $key2 => $rabItem) {
                $this->rabStyleArr[] = [
                    'pointer' => $pointer,
                    'type' => self::RAB_ITEM_CONTENT
                ];
                $pointer++;

                // 1.A) Handle RAB item with no AHS attached
                if ($rabItem->customAhs == null) {
                    $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                    $rabs[$key]->rabItem[$key2] = $rabItem;
                    $rabSubtotal += $rabItem->subtotal;
                    $rabPerSectionCount += $rabItem->subtotal;
                    continue;
                }

                // 1.B) Handle RAB item with AHS attached
                $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                $countedAhs->price = round($countedAhs->subtotal * (1 + ($this->project->profit_margin/100)));
                $rabItem->subtotal = $countedAhs->price;
                $rabs[$key]->rabItem[$key2]['custom_ahs'] = $countedAhs;
                $rabSubtotal += $countedAhs->price;
                $rabPerSectionCount += $countedAhs->price * $rabItem->volume;
            }

            // 2) Handle RAB item with header
            foreach ($rab->rabItemHeader as $key3 => $rabItemHeader) {
                $this->rabStyleArr[] = [
                    'pointer' => $pointer,
                    'type' => self::RAB_ITEM_HEADER
                ];
                $pointer++;

                foreach ($rabItemHeader->rabItem as $key4 => $rabItem) {
                    $this->rabStyleArr[] = [
                        'pointer' => $pointer,
                        'type' => self::RAB_ITEM_CONTENT
                    ];
                    $pointer++;

                    // 2.A) Handle RAB item with no AHS attached
                    if ($rabItem->customAhs == null) {
                        $rabItem->subtotal = $rabItem->price * ($rabItem->volume ?? 0);
                        $rabSubtotal += $rabItem->subtotal;
                        $rabPerSectionCount += $rabItem->subtotal;
                        continue;
                    }

                    // 2.B) Handle RAB item with AHS attached
                    $countedAhs = $this->countCustomAhsSubtotal($rabItem->customAhs);
                    $countedAhs->price = round($countedAhs->subtotal * (1 + ($this->project->profit_margin/100)));
                    $rabItem->subtotal = $countedAhs->price;
                    $rabs[$key]->rabItemHeader[$key3]->rabItem[$key4]['custom_ahs'] = $countedAhs;
                    $rabSubtotal += $countedAhs->price;
                    $rabPerSectionCount += $countedAhs->price * $rabItem->volume;
                }
            }
            $rab->subtotal = $rabPerSectionCount;
        }
        
        $this->finalPointerLocation = $pointer - 1;

        return $rabs;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 7,
            'C' => 8,
            'D' => 19,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 12,
            'I' => 7
        ];
    }
}
