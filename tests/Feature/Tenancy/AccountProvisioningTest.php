<?php

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration provisions a tenant account and owner membership', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Ana Gomez',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'ana@example.com')->firstOrFail();

    expect($user->current_account_id)->not->toBeNull();

    $account = Account::query()->findOrFail($user->current_account_id);

    expect($account->name)->toBe("Ana Gomez's Workspace");
    expect($account->slug)->toBe('ana-gomez');

    $this->assertDatabaseHas('account_user', [
        'account_id' => $account->id,
        'user_id' => $user->id,
        'is_owner' => true,
    ]);

    $adminRole = Role::query()
        ->where('account_id', $account->id)
        ->where('name', RolePermissionMatrix::AdministratorRole)
        ->where('guard_name', 'web')
        ->first();

    expect($adminRole)->not->toBeNull();

    $this->assertDatabaseHas('model_has_roles', [
        'account_id' => $account->id,
        'role_id' => $adminRole->id,
        'model_type' => User::class,
        'model_id' => $user->id,
    ]);

    $this->assertDatabaseHas('permissions', [
        'name' => 'deals.move',
        'guard_name' => 'web',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'auth',
        'event' => 'registered',
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

test('registration creates a unique account slug for duplicate names', function () {
    Account::factory()->create([
        'name' => "Ana Gomez's Workspace",
        'slug' => 'ana-gomez',
    ]);

    $response = $this->post(route('register.store'), [
        'name' => 'Ana Gomez',
        'email' => 'ana+2@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'ana+2@example.com')->firstOrFail();
    $account = Account::query()->findOrFail($user->current_account_id);

    expect($account->slug)->toBe('ana-gomez-2');
});
