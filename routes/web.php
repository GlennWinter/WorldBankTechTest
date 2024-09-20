<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorldBankController;

// Get routes
Route::get('/', function () {
    return view('worldBank');
})->name('home');


// Post routes
Route::post('/get-all-countries', [WorldBankController::class, 'getAllCountries'])->name('getAllCountries');
Route::post('/search', [WorldBankController::class, 'search'])->name('search');

