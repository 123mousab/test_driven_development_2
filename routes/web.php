<?php

use Illuminate\Support\Facades\Route;




Route::get('mockups/order', function (){
    return view('orders.show');
});

Route::get('/concerts/{id}', [\App\Http\Controllers\ConcertsController::class, 'show']);

Route::post('/concerts/{id}/orders', [\App\Http\Controllers\ConcertsOrdersController::class, 'store']);

Route::get('/orders/{confirmationNumber}', [\App\Http\Controllers\OrderController::class, 'show']);
