<?php

namespace App\Providers;


use App\Billing\PaymentGateway;
use App\Models\Order;

class TicketPurchasingService
{
    private $paymentGateway;

    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function purchaseTickets($concert, $ticketQuantity, $email, $paymentToken)
    {
        $reservation = $concert->reserveTickets($ticketQuantity, $email);
        $this->paymentGateway->charge($reservation->totalCost(), $paymentToken);

        return Order::forTickets($reservation->tickets(), $reservation->email(), $reservation->totalCost());
    }
}
