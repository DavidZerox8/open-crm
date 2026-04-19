<?php

use App\Models\Account;
use App\Models\CRM\Company;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Lead;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use App\Models\User;
use App\Support\Authorization\RolePermissionMatrix;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'current_account_id' => $this->account->id,
    ]);

    $this->user->accounts()->attach($this->account->id, ['is_owner' => true]);

    $permissionRegistrar = app(PermissionRegistrar::class);
    $permissionRegistrar->setPermissionsTeamId($this->account->id);
    $permissionRegistrar->forgetCachedPermissions();

    app(RolePermissionMatrix::class)->ensureRolesForAccount($this->account);
    $this->user->assignRole(RolePermissionMatrix::AdministratorRole);

    $permissionRegistrar->setPermissionsTeamId(null);
    $permissionRegistrar->forgetCachedPermissions();

    $this->lead = Lead::factory()->create([
        'account_id' => $this->account->id,
        'owner_id' => $this->user->id,
    ]);

    $this->company = Company::factory()->create([
        'account_id' => $this->account->id,
        'owner_id' => $this->user->id,
    ]);

    $this->contact = Contact::factory()->create([
        'account_id' => $this->account->id,
        'company_id' => $this->company->id,
        'owner_id' => $this->user->id,
    ]);

    $this->pipeline = Pipeline::factory()->create([
        'account_id' => $this->account->id,
        'is_default' => true,
        'position' => 0,
    ]);

    $this->stage = PipelineStage::factory()->create([
        'account_id' => $this->account->id,
        'pipeline_id' => $this->pipeline->id,
        'name' => 'Qualified',
        'slug' => 'qualified',
        'position' => 1,
    ]);

    $this->deal = Deal::factory()->create([
        'account_id' => $this->account->id,
        'pipeline_id' => $this->pipeline->id,
        'stage_id' => $this->stage->id,
        'company_id' => $this->company->id,
        'contact_id' => $this->contact->id,
        'owner_id' => $this->user->id,
    ]);
});

test('crm index pages are accessible for a user with crm permissions', function () {
    $this->actingAs($this->user);

    $this->get(route('crm.dashboard'))->assertSuccessful();
    $this->get(route('crm.leads.index'))->assertSuccessful();
    $this->get(route('crm.companies.index'))->assertSuccessful();
    $this->get(route('crm.contacts.index'))->assertSuccessful();
    $this->get(route('crm.pipeline.board'))->assertSuccessful();
    $this->get(route('crm.tasks.index'))->assertSuccessful();
    $this->get(route('crm.reports.index'))->assertSuccessful();
});

test('crm detail pages are accessible for account scoped records', function () {
    $this->actingAs($this->user);

    $this->get(route('crm.leads.show', $this->lead))->assertSuccessful();
    $this->get(route('crm.companies.show', $this->company))->assertSuccessful();
    $this->get(route('crm.contacts.show', $this->contact))->assertSuccessful();
    $this->get(route('crm.deals.show', $this->deal))->assertSuccessful();
});

test('crm dashboard is forbidden for users without crm permissions', function () {
    $member = User::factory()->create([
        'email_verified_at' => now(),
        'current_account_id' => $this->account->id,
    ]);

    $member->accounts()->attach($this->account->id, ['is_owner' => false]);

    $this->actingAs($member);

    $this->get(route('crm.dashboard'))->assertForbidden();
});
