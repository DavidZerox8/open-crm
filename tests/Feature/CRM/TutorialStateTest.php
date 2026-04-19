<?php

use App\Models\Account;
use App\Models\CrmTutorialState;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->account = Account::factory()->create();
    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'current_account_id' => $this->account->id,
    ]);

    $this->user->accounts()->attach($this->account->id, ['is_owner' => true]);
    $this->actingAs($this->user);
});

test('crm tutorial state is initialized for the current account', function () {
    $response = $this->get(route('crm.tutorial.state'));

    $response
        ->assertOk()
        ->assertJsonPath('tutorial_key', 'crm-onboarding')
        ->assertJsonPath('tutorial_version', 1)
        ->assertJsonPath('completed_modules', [])
        ->assertJsonPath('dismissed', false)
        ->assertJsonPath('completed', false)
        ->assertJsonPath('should_start', true);

    expect(CrmTutorialState::query()->count())->toBe(1);
});

test('crm tutorial can be completed module by module', function () {
    $state = $this->get(route('crm.tutorial.state'))->json();
    $modules = $state['modules'];

    foreach ($modules as $module) {
        $response = $this->postJson(route('crm.tutorial.state.update'), [
            'action' => 'complete-module',
            'module' => $module,
        ]);
    }

    $response
        ->assertOk()
        ->assertJsonPath('completed', true)
        ->assertJsonPath('should_start', false);

    $tutorialState = CrmTutorialState::query()->firstOrFail();

    expect($tutorialState->completed_at)->not->toBeNull();
    expect($tutorialState->dismissed_at)->toBeNull();
});

test('crm tutorial can be skipped and restarted', function () {
    $skip = $this->postJson(route('crm.tutorial.state.update'), [
        'action' => 'skip',
    ]);

    $skip
        ->assertOk()
        ->assertJsonPath('dismissed', true)
        ->assertJsonPath('should_start', false);

    $restart = $this->postJson(route('crm.tutorial.restart'));

    $restart
        ->assertOk()
        ->assertJsonPath('completed_modules', [])
        ->assertJsonPath('dismissed', false)
        ->assertJsonPath('completed', false)
        ->assertJsonPath('should_start', true);

    $tutorialState = CrmTutorialState::query()->firstOrFail();

    expect($tutorialState->dismissed_at)->toBeNull();
    expect($tutorialState->completed_at)->toBeNull();
});

test('crm tutorial state is isolated per account for the same user', function () {
    $secondAccount = Account::factory()->create();
    $this->user->accounts()->attach($secondAccount->id, ['is_owner' => false]);

    $this->postJson(route('crm.tutorial.state.update'), [
        'action' => 'complete-module',
        'module' => 'dashboard',
    ])->assertOk();

    $this->user->forceFill(['current_account_id' => $secondAccount->id])->save();

    $response = $this->get(route('crm.tutorial.state'));

    $response
        ->assertOk()
        ->assertJsonPath('completed_modules', [])
        ->assertJsonPath('dismissed', false)
        ->assertJsonPath('completed', false)
        ->assertJsonPath('should_start', true);

    expect(CrmTutorialState::query()->count())->toBe(2);
});

test('crm tutorial update requires a known module when completing', function () {
    $response = $this->postJson(route('crm.tutorial.state.update'), [
        'action' => 'complete-module',
        'module' => 'unknown-module',
    ]);

    $response->assertStatus(422);
});
