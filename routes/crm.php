<?php

use App\Http\Controllers\Account\SwitchAccountController;
use App\Http\Controllers\CRM\TutorialStateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/accounts/switch', SwitchAccountController::class)->name('accounts.switch');

    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('tutorial/state', [TutorialStateController::class, 'show'])->name('tutorial.state');
        Route::post('tutorial/state', [TutorialStateController::class, 'update'])->name('tutorial.state.update');
        Route::post('tutorial/restart', [TutorialStateController::class, 'restart'])->name('tutorial.restart');

        Route::livewire('/', 'pages::crm.dashboard')->name('dashboard');

        Route::livewire('leads', 'pages::crm.leads.index')->name('leads.index');
        Route::livewire('leads/{lead}', 'pages::crm.leads.show')->name('leads.show');

        Route::livewire('companies', 'pages::crm.companies.index')->name('companies.index');
        Route::livewire('companies/{company}', 'pages::crm.companies.show')->name('companies.show');

        Route::livewire('contacts', 'pages::crm.contacts.index')->name('contacts.index');
        Route::livewire('contacts/{contact}', 'pages::crm.contacts.show')->name('contacts.show');

        Route::livewire('pipeline', 'pages::crm.pipeline.board')->name('pipeline.board');
        Route::livewire('deals/{deal}', 'pages::crm.deals.show')->name('deals.show');

        Route::livewire('tasks', 'pages::crm.tasks.index')->name('tasks.index');
        Route::livewire('reports', 'pages::crm.reports.index')->name('reports.index');
    });
});
