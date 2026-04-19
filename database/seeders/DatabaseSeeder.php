<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $account = Account::factory()->create([
            'name' => 'Open CRM',
            'slug' => 'open-crm',
        ]);

        app(RolePermissionMatrix::class)->ensureRolesForAccount($account);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'current_account_id' => $account->id,
        ]);

        $user->accounts()->attach($account->id, ['is_owner' => true]);

        $permissionRegistrar = app(PermissionRegistrar::class);
        $previousTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($account->id);

        $user->assignRole(app(RolePermissionMatrix::class)->ownerRoleName());

        $permissionRegistrar->setPermissionsTeamId($previousTeamId);
    }
}
