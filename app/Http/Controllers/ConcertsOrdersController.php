<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
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
        $concert = Concert::query()->published()->findOrFail($concertId);

        $this->validate(request(), [
            'email' => ['required', 'email'],
            'ticket_quantity' => ['required', 'numeric', 'min:1'],
            'payment_token' => ['required'],
        ]);

        try {
            $reservation = $concert->reserveTickets(request('ticket_quantity'), \request('email'));
            $order = $reservation->complete($this->paymentGateway, \request('payment_token'));

            return response()->json([
                'email' => $order->email,
                'ticket_quantity' => $order->ticketQuantity(),
                'amount' => request('ticket_quantity') * $concert->ticket_price
            ], 201);

        } catch (PaymentFailedException $exception) {
            $reservation->cancel();
            return response()->json([], 422);
        } catch (NotEnoughTicketsException $exception) {
            return response()->json([], 422);
        }
    }
}
