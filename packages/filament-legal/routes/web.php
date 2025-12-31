<?php

use BeeGoodIT\FilamentLegal\Http\Controllers\LegalAcceptanceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/legal/accept', [LegalAcceptanceController::class, 'show'])->name('filament-legal.acceptance');
    Route::post('/legal/accept', [LegalAcceptanceController::class, 'accept'])->name('filament-legal.submit-acceptance');
});
