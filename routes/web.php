<?php

use Illuminate\Support\Facades\Route;




Route::get('/concerts/{id}', [\App\Http\Controllers\ConcertsController::class, 'show']);

Route::post('/concerts/{id}/orders', [\App\Http\Controllers\ConcertsOrdersController::class, 'store']);

Route::post('/concerts/orders', function (){
    dd('test');
});
