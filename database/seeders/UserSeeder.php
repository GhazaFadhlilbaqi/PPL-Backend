<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
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

        // User::truncate();

        $rootUsers = User::factory()->create([
            'first_name' => 'Root',
            'last_name' => 'Account',
            'password' => Hash::make(1234),
            'email' => 'root@gmail.com',
            'email_verified_at' => Carbon::now(),
            'address' => 'Jln. Pahlawan No. 7, Jakarta'
        ]);

        $rootUsers->each(function($user) {
            $user->assignRole('root');
        });

        $ownerUsers = User::factory()->create([
            'password' => Hash::make(1234),
            'email' => 'owner@gmail.com',
            'email_verified_at' => Carbon::now(),
        ]);

        $ownerUsers->each(function($user) {
            $user->assignRole('owner');
        });

        // $users = User::factory(10)->create();

        // $users->each(function($user) {
        //     $user->assignRole('owner');
        // });
    }
}
