<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Create roles
        $roles = $this->createRoles();

        // Create permissions
        $this->createPermissions();

        $rootPermissions = [
            'read dashboard'
        ];

        $ownerPermission = [
            'read dashboard'
        ];

        $consultanPermissions = [
            'read dashboard'
        ];

        // Assigning Permission
        $roles['root']->givePermissionTo($rootPermissions);
        $roles['owner']->givePermissionTo($ownerPermission);
        $roles['consultant']->givePermissionTo($consultanPermissions);
    }

    private function createRoles()
    {
        return [
            'root' => Role::create([
                'name' => 'root',
                'guard_name' => 'api'
            ]),
            'owner' => Role::create([
                'name' => 'owner',
                'guard_name' => 'api'
            ]),
            'consultant' => Role::create([
                'name' => 'consultant',
                'guard_name' => 'api'
            ]),
        ];
    }

    private function createPermissions()
    {
        return Permission::insert([
            ['name' => 'read dashboard', 'guard_name' => 'api']
        ]);
    }
}
