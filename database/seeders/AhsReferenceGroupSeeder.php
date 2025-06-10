<?php

namespace Database\Seeders;

use App\Models\AhsReferenceGroup;
use Illuminate\Database\Seeder;

class AhsReferenceGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AhsReferenceGroup::insert([
            [
                'code' => 'reference',
                'name' => 'Permen PUPR 2016',
                'deleted_at' => now()
            ],
            [
                'code' => 'reference-2023',
                'name' => 'Permen PUPR 2023',
                'deleted_at' => now()
            ],
            [
                'code' => 'reference-2024',
                'name' => 'Permen PUPR 2024',
                'deleted_at' => null
            ],
            [
                'code' => 'custom-reference',
                'name' => 'AHSP Rencanakan',
                'deleted_at' => null
            ],
        ]);
    }
}
