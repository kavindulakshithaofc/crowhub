<?php

use App\Http\Controllers\QuoteTotalsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::post('admin/quotes/{quote}/recalculate', QuoteTotalsController::class)
        ->name('admin.quotes.recalculate');
});

require __DIR__.'/settings.php';
