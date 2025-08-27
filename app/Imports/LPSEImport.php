<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\Rab;
use App\Models\RabItem;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\RabItemHeader;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class LPSEImport implements ToCollection, WithStartRow, WithEvents
{
    protected $project;
    protected $firstCellValue;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function startRow(): int
    {
        return 8;
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                $sheet = $event->reader->getDelegate()->getActiveSheet();
                $this->firstCellValue = trim((string) $sheet->getCell('A1')->getValue());
            },
        ];
    }

    public function collection(Collection $rows)
    {
        // Find existing RABs
        $existingRabs = Rab::where('project_id', $this->project->id)->get();
    
        foreach ($existingRabs as $rab) {
            // Delete related RabItems and RabItemHeaders
            $rab->rabItem()->delete();
            $rab->rabItemHeader()->delete();
            $rab->delete();
        }
    
        $currentRab = null;
        $currentHeader = null;

        $isPossibleHeader = empty($satuan);
        
        if ($isPossibleHeader) {
            $currentRab = Rab::create([
                'project_id' => $this->project->id,
                'name' => $this->firstCellValue ?? "RAB APENDO LPSE",
            ]);
        }

        foreach ($rows as $i => $row) {
            $uraian = trim((string) ($row[0] ?? ''));
            $volume = str_replace(',', '.', (string) ($row[2] ?? ''));
            $satuan = trim((string) ($row[1] ?? ''));
            // $hargaSatuan = preg_replace('/[^\d.]/', '', (string) ($row[5] ?? ''));

            if ($uraian === '') {
                continue;
            }        
                        
            if (str_contains(strtolower($uraian), 'total')) continue;
        
            $unit = \App\Models\Unit::whereRaw('LOWER(name) = ?', [strtolower($satuan)])->first();
            if (!$unit) {
                $unit = \App\Models\Unit::find(1);
            }
        
            \App\Models\RabItem::create([
                'rab_id' => $currentRab->id,
                'rab_item_header_id' => $currentHeader?->id,
                'name' => $uraian,
                'volume' => is_numeric($volume) ? (float) $volume : 0,
                // 'price' => is_numeric($hargaSatuan) ? (float) $hargaSatuan : 0,
                'unit_id' => $unit->id,
            ]);        
        }
    }
}