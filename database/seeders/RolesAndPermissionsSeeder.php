<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function __construct(private readonly RolePermissionMatrix $rolePermissionMatrix) {}

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->rolePermissionMatrix->ensurePermissionCatalog();

        Account::query()->each(function (Account $account): void {
            $this->rolePermissionMatrix->ensureRolesForAccount($account);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
