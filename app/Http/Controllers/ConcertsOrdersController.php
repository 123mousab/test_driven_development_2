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
        $concert = Concert::query()->findOrFail($concertId);
        $ticketQuantity = \request('ticket_quantity');
        $token = \request('payment_token');
        $amount = $ticketQuantity * $concert->ticket_price;
        $this->paymentGateway->charge($amount, $token);
        return response()->json([], 201);
    }
}
