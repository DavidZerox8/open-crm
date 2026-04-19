<?php

namespace App\Support\Authorization;

use App\Models\Account;
use App\Models\Permission;
use App\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionMatrix
{
    /**
     * The administrator role name.
     */
    public const AdministratorRole = 'Administrator';

    /**
     * The manager role name.
     */
    public const ManagerRole = 'Manager';

    /**
     * The executive role name.
     */
    public const ExecutiveRole = 'Executive';

    /**
     * Get the role that should be assigned to account owners.
     */
    public function ownerRoleName(): string
    {
        return self::AdministratorRole;
    }

    /**
     * Get the complete list of granular CRM permissions.
     *
     * @return array<int, string>
     */
    public function permissions(): array
    {
        return [
            'accounts.view',
            'accounts.update',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
            'permissions.view',
            'permissions.update',
            'leads.view',
            'leads.create',
            'leads.update',
            'leads.delete',
            'leads.convert',
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',
            'contacts.view',
            'contacts.create',
            'contacts.update',
            'contacts.delete',
            'deals.view',
            'deals.create',
            'deals.update',
            'deals.delete',
            'deals.move',
            'pipeline.view',
            'pipeline.manage',
            'activities.view',
            'activities.create',
            'activities.update',
            'activities.delete',
            'tasks.view',
            'tasks.create',
            'tasks.update',
            'tasks.delete',
            'tasks.assign',
            'tasks.complete',
            'reports.view',
            'settings.manage',
        ];
    }

    /**
     * Get role-to-permissions matrix.
     *
     * @return array<string, array<int, string>>
     */
    public function roles(): array
    {
        return [
            self::AdministratorRole => $this->permissions(),
            self::ManagerRole => [
                'accounts.view',
                'users.view',
                'users.create',
                'users.update',
                'roles.view',
                'permissions.view',
                'leads.view',
                'leads.create',
                'leads.update',
                'leads.convert',
                'companies.view',
                'companies.create',
                'companies.update',
                'contacts.view',
                'contacts.create',
                'contacts.update',
                'deals.view',
                'deals.create',
                'deals.update',
                'deals.move',
                'pipeline.view',
                'activities.view',
                'activities.create',
                'activities.update',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                'tasks.assign',
                'tasks.complete',
                'reports.view',
            ],
            self::ExecutiveRole => [
                'leads.view',
                'leads.create',
                'leads.update',
                'leads.convert',
                'companies.view',
                'contacts.view',
                'contacts.create',
                'contacts.update',
                'deals.view',
                'deals.create',
                'deals.update',
                'deals.move',
                'pipeline.view',
                'activities.view',
                'activities.create',
                'tasks.view',
                'tasks.create',
                'tasks.update',
                'tasks.complete',
            ],
        ];
    }

    /**
     * Ensure permission catalog and account-scoped roles are provisioned.
     */
    public function ensureRolesForAccount(Account $account, string $guardName = 'web'): void
    {
        $this->ensurePermissionCatalog($guardName);

        $permissionRegistrar = app(PermissionRegistrar::class);
        $previousTeamId = $permissionRegistrar->getPermissionsTeamId();

        $permissionRegistrar->setPermissionsTeamId($account->id);

        try {
            foreach ($this->roles() as $roleName => $permissions) {
                $role = Role::findOrCreate($roleName, $guardName);
                $role->syncPermissions($permissions);
            }
        } finally {
            $permissionRegistrar->setPermissionsTeamId($previousTeamId);
        }
    }

    /**
     * Ensure global permission catalog exists.
     */
    public function ensurePermissionCatalog(string $guardName = 'web'): void
    {
        foreach ($this->permissions() as $permission) {
            Permission::findOrCreate($permission, $guardName);
        }
    }
}
