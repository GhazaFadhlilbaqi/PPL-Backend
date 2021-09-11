<?php

namespace Database\Seeders;

use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Unit::insert([
            [
                'name' => 'OH',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'm3',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'liter',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'zak',
                'created_at' => Carbon::now(),
            ],
            [
                'name' => 'kg',
                'created_at' => Carbon::now(),
            ],
        ]);
    }
}
