<?php

namespace App\Exports;

use App\Models\CustomItemPriceGroup;
use App\Models\ItemPrice;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class ItemPriceExportSheet implements FromView, WithTitle
{

    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function view() : View
    {

        $customItemPriceGroups = CustomItemPriceGroup::where('project_id', $this->projectId)->get();

        return view('exports.rab.item-price', [
            'customItemPricesGroups' => $customItemPriceGroups
        ]);
    }

    public function title() : string
    {
        return 'Harga Satuan';
    }
}
