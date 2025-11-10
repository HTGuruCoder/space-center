<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
    [
        'prefix' => LaravelLocalization::setLocale(),
        'middleware' => ['localeCookieRedirect', 'localizationRedirect', 'localeViewPath'],
    ], function () {
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->name('app.livewire.update');
        });

        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
            Route::get('/register', \App\Livewire\Auth\EmployeeRegister::class)->name('register');
        });

        // Test route for component development
        Route::get('/test', \App\Livewire\Test::class)->name('test');
    });
