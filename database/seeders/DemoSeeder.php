<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DemoSeeder extends Seeder
{
    private const DemoAccountName = 'Open CRM';

    private const DemoAccountSlug = 'open-crm';

    private const DemoOwnerName = 'Demo Owner';

    private const DemoOwnerEmail = 'demo@opencrm.test';

    private const DemoOwnerPassword = 'password';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $account = Account::query()->firstOrCreate(
            ['slug' => self::DemoAccountSlug],
            ['name' => self::DemoAccountName],
        );

        $rolePermissionMatrix = app(RolePermissionMatrix::class);
        $rolePermissionMatrix->ensureRolesForAccount($account);

        $owner = User::query()->firstOrCreate(
            ['email' => self::DemoOwnerEmail],
            [
                'name' => self::DemoOwnerName,
                'password' => self::DemoOwnerPassword,
                'email_verified_at' => now(),
                'current_account_id' => $account->id,
            ],
        );

        $owner->forceFill([
            'name' => self::DemoOwnerName,
            'password' => self::DemoOwnerPassword,
            'email_verified_at' => now(),
            'current_account_id' => $account->id,
        ])->save();

        $owner->accounts()->syncWithoutDetaching([
            $account->id => ['is_owner' => true],
        ]);

        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->forgetCachedPermissions();

        $previousTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($account->id);

        try {
            $owner->assignRole($rolePermissionMatrix->ownerRoleName());
        } finally {
            $permissionRegistrar->setPermissionsTeamId($previousTeamId);
            $permissionRegistrar->forgetCachedPermissions();
        }

        if (! $this->accountHasDemoData($account)) {
            (new DemoCrmSeeder)->run($account, $owner);
        }

        if ($this->command !== null) {
            $this->command->info('Demo account ready.');
            $this->command->line('Email: '.self::DemoOwnerEmail);
            $this->command->line('Password: '.self::DemoOwnerPassword);
        }
    }

    protected function accountHasDemoData(Account $account): bool
    {
        return $account->pipelines()->exists()
            || $account->companies()->exists()
            || $account->contacts()->exists()
            || $account->leads()->exists()
            || $account->deals()->exists()
            || $account->activities()->exists()
            || $account->tasks()->exists();
    }
}
