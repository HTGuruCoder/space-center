<?php

use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

Route::group(
[
	'prefix' => LaravelLocalization::setLocale(),
	'middleware' => [ 'localeCookieRedirect', 'localizationRedirect', 'localeViewPath' ]
], function(){ //...
});
