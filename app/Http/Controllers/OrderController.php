<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function show($confirmationNumber)
    {
        return response()->json([], 200);
    }
}
