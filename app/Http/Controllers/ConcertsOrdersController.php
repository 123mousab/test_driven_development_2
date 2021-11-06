<?php

namespace App\Http\Controllers;

use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Http\Request;

class ConcertsOrdersController extends Controller
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store($concertId)
    {
        $this->validate(request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
            'payment_token' => ['required'],
        ]);

        $concert = Concert::query()->find($concertId);

        // Charging the customer
        $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, \request('payment_token'));

        // Creating the order
       $order = $concert->orderTickets(\request('email'), request('ticket_quantity'));

        return response()->json([], 201);
    }
}
