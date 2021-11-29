<?php

use Illuminate\Support\Facades\Route;




Route::get('/', function (){
    return 'laravel';
});

Route::get('mockups/order', function (){
    return view('orders.show');
});

Route::get('/concerts/{id}', [\App\Http\Controllers\ConcertsController::class, 'show'])->name('concerts.show');
Route::post('/concerts/{id}/orders', [\App\Http\Controllers\ConcertsOrdersController::class, 'store']);
Route::get('/orders/{confirmationNumber}', [\App\Http\Controllers\OrderController::class, 'show']);

Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login');
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('auth.show-login');
Route::post('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('auth.logout');

Route::prefix('backstage')->middleware(['auth'])->group(function() {
    Route::get('concerts/new', [\App\Http\Controllers\Backstage\ConcertController::class, 'create']);
    Route::post('concerts', [\App\Http\Controllers\Backstage\ConcertController::class, 'store']);
});
