<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\WorkOS\Http\Middleware\ValidateSessionWithWorkOS;

// For liveness probe
Route::get('/healthz', function () {
    return response()->json(['status' => 'healthy', 'code' => 200], 200);
});

// Route::get('/', function () {
//     return Inertia::render('welcome');
// })->name('home');

// Route::middleware([
//     'auth',
//     ValidateSessionWithWorkOS::class,
// ])->group(function () {
//     Route::get('dashboard', function () {
//         return Inertia::render('dashboard');
//     })->name('dashboard');
// });

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
