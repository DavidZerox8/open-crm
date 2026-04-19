<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('successful login is recorded in activity log', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'auth',
        'event' => 'login',
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

test('logout is recorded in activity log', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('logout'));

    $response->assertRedirect('/');

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'auth',
        'event' => 'logout',
        'causer_type' => User::class,
        'causer_id' => $user->id,
        'subject_type' => User::class,
        'subject_id' => $user->id,
    ]);
});

test('failed authentication attempts are recorded in activity log', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $response = $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'invalid-password',
        ]);

    $response->assertRedirect(route('login'));

    $this->assertDatabaseHas('activity_log', [
        'log_name' => 'auth',
        'event' => 'failed',
        'description' => 'Authentication failed',
    ]);
});
