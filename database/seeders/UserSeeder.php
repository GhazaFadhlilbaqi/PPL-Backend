<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        User::truncate();

        User::factory()->create([
            'name' => 'Mizuhara Chizuru',
            'email' => 'chizuru@gmail.com',
            'password' => Hash::make(1234),
        ]);

        User::factory(10)->create();
    }
}
