<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function show($confirmationNumber)
    {
        $order = Order::query()->where('confirmation_number', $confirmationNumber)->firstOrFail();

        return view('orders.show', compact('order'));
    }
}
