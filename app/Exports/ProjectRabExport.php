<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectRabExport implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }

    public function sheets(): array {
        $sheets = [];

        for ()

        return $sheets;
    }
}
