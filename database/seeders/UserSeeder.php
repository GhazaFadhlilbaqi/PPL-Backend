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

        $rootUser = User::factory()->create([
            'first_name' => 'Mizuhara',
            'last_name' => 'Chizuru',
            'email' => 'chizuru@gmail.com',
            'address' => 'Jln. Pahlawan No. 7 Gambiran, Banyuwangi'
        ]);

        $rootUser->each(function($user) {
            $user->assignRole('root');
        });

        $users = User::factory(10)->create();

        $users->each(function($user) {
            $user->assignRole('owner');
        });
    }
}
