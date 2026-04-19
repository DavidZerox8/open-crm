<?php

use Laravel\Fortify\Features;

test('public registration is disabled', function () {
    expect(config('fortify.features'))->not->toContain(Features::registration());

    $this->get('/register')->assertNotFound();
    $this->post('/register')->assertNotFound();
});

test('home page shows demo credentials', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('demo@opencrm.test', false)
        ->assertSee('password', false)
        ->assertDontSee('Crear cuenta', false);
});
