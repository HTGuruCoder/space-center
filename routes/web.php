<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
[
	'prefix' => LaravelLocalization::setLocale(),
	'middleware' => [ 'localeCookieRedirect', 'localizationRedirect', 'localeViewPath' ]
], function(){
	// Test route for component development
	Route::get('/test', \App\Livewire\Test::class)->name('test');
});
