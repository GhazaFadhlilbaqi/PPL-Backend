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

class RabExcelImport implements ToCollection, WithStartRow
{
    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function startRow(): int
    {
        return 12;
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

        foreach ($rows as $i => $row) {
            $no = trim((string) ($row[0] ?? ''));
            $uraian = trim((string) ($row[1] ?? ''));
            $volume = str_replace(',', '.', (string) ($row[3] ?? ''));
            $satuan = trim((string) ($row[4] ?? ''));
            $hargaSatuan = preg_replace('/[^\d.]/', '', (string) ($row[5] ?? ''));
                
            $result = preg_match('/^[IVXLCDM]+$/', $no);

            $isPossibleHeader = !empty($no) &&
            preg_match('/^[IVXLCDM]+$/', $no) &&
            empty($row[2]) && empty($row[3]) && empty($row[4]) && empty($row[5]);
        
            if ($isPossibleHeader) {
                if ($currentRab) {
                    $currentHeader = RabItemHeader::create([
                        'rab_id' => $currentRab->id,
                        'name' => $uraian,
                    ]);
                } else {
                }
                continue;
            }

            if ($uraian && !is_numeric($no)) {
                $currentRab = Rab::create([
                    'project_id' => $this->project->id,
                    'name' => $uraian,
                ]);
                $currentHeader = null;
                continue;
            }
        
            if (!$currentRab) {
                continue;
            }
        
            if (str_contains(strtolower($uraian), 'total')) continue;
        
            $unit = \App\Models\Unit::whereRaw('LOWER(name) = ?', [strtolower($satuan)])->first();
            if (!$unit) {
                continue;
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
