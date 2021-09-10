<?php

namespace Database\Seeders;

use App\Models\Province;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $provincesJson = collect(json_decode(File::get(storage_path('external-data/provinces.json'))))->map(function ($data) {
            return [
                'id' => $data->id,
                'name' => $data->name,
                'created_at' => Carbon::now()
            ];
        })->toArray();

        Province::insert($provincesJson);
    }
}
