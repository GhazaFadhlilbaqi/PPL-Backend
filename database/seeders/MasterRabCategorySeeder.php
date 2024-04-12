<?php

namespace Database\Seeders;

use App\Models\MasterRabCategory;
use Illuminate\Database\Seeder;

class MasterRabCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        MasterRabCategory::create([
            'name' => 'Tanpa Kategori',
            'is_default' => true,
        ]);
    }
}
