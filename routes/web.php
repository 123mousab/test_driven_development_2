<?php

use Illuminate\Support\Facades\Route;




Route::get('/concerts/{id}', [\App\Http\Controllers\ConcertsController::class, 'show']);
