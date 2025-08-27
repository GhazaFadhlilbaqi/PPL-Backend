<?php

namespace App\Exports;

use App\Models\Project;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminProjectExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function headings(): array {
      return [
        "First Name",
        "Last Name",
        "Occupation",
        "Joined Date",
        "Last Open Date",
        "Email",
        "Phone",
        "Project Name",
        "Project Location",
        "Fiscal Year"
      ];
    }

    public function collection() {
      $projects = Project::with('user', 'province')->get();
      return $projects
        ->sortByDesc(fn($project) => $project->user->created_at)
        ->map(function ($project) {
            return [
              $project->user->first_name ?? '',
              $project->user->last_name ?? '',
              $project->user->job ?? '',
              Carbon::parse($project->user->created_at)->format('d-m-Y'),
              Carbon::parse($project->last_opened_at)->format('d-m-Y'),
              $project->user->email ?? '',  
              $project->user->phone ?? '',
              $project->name,
              $project->province->name,
              $project->fiscal_year
            ];
        });
    }
}