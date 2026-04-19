<?php

use App\Models\Account;
use App\Models\Role;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('demo seeder provisions account, owner credentials, and crm data', function () {
    Artisan::call('db:seed', [
        '--class' => DemoSeeder::class,
        '--no-interaction' => true,
    ]);

    $account = Account::query()->where('slug', 'open-crm')->first();
    expect($account)->not->toBeNull();

    $owner = User::query()->where('email', 'demo@opencrm.test')->first();
    expect($owner)->not->toBeNull();

    expect($owner->current_account_id)->toBe($account->id);
    expect($owner->email_verified_at)->not->toBeNull();
    expect(Hash::check('password', $owner->password))->toBeTrue();
    expect($owner->accounts()->whereKey($account->id)->exists())->toBeTrue();

    $administratorRole = Role::query()
        ->where('account_id', $account->id)
        ->where('name', RolePermissionMatrix::AdministratorRole)
        ->first();

    expect($administratorRole)->not->toBeNull();

    expect(DB::table('model_has_roles')->where([
        'account_id' => $account->id,
        'role_id' => $administratorRole->id,
        'model_type' => User::class,
        'model_id' => $owner->id,
    ])->exists())->toBeTrue();

    expect($account->pipelines()->exists())->toBeTrue();
    expect($account->companies()->exists())->toBeTrue();
    expect($account->contacts()->exists())->toBeTrue();
    expect($account->leads()->exists())->toBeTrue();
    expect($account->deals()->exists())->toBeTrue();
    expect($account->activities()->exists())->toBeTrue();
    expect($account->tasks()->exists())->toBeTrue();
});

test('demo seeder can run multiple times without duplicating demo account or user', function () {
    Artisan::call('db:seed', [
        '--class' => DemoSeeder::class,
        '--no-interaction' => true,
    ]);
    Artisan::call('db:seed', [
        '--class' => DemoSeeder::class,
        '--no-interaction' => true,
    ]);

    expect(Account::query()->where('slug', 'open-crm')->count())->toBe(1);
    expect(User::query()->where('email', 'demo@opencrm.test')->count())->toBe(1);
});
