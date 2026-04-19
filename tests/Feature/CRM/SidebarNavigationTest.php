<?php

use App\Models\Account;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

test('crm sidebar removes duplicated settings group and keeps native settings entry', function () {
    $account = Account::factory()->create();
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'current_account_id' => $account->id,
    ]);

    $user->accounts()->attach($account->id, ['is_owner' => true]);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($account->id);
    $permissionRegistrar->forgetCachedPermissions();

    app(RolePermissionMatrix::class)->ensureRolesForAccount($account);
    $user->assignRole(RolePermissionMatrix::AdministratorRole);

    $permissionRegistrar->setPermissionsTeamId(null);
    $permissionRegistrar->forgetCachedPermissions();

    $response = $this->actingAs($user)->get(route('crm.dashboard'));

    $response->assertOk();

    $response->assertDontSee(route('security.edit'), false);
    $response->assertDontSee(route('appearance.edit'), false);
    $response->assertSee(route('profile.edit'), false);
    $response->assertDontSee('data-flux-sidebar-item="data-flux-sidebar-item" data-test="logout-button"', false);
    $response->assertSee('data-tour="crm-sidebar"', false);
    $response->assertSee('data-tour="dashboard-kpis"', false);
    $response->assertSee('data-tour-launcher', false);
});
