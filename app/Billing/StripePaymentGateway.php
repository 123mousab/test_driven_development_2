<?php


namespace App\Billing;


class StripePaymentGateway implements PaymentGateway
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function charge($amount, $token)
    {
        \Stripe\Charge::create(
            [
                'amount' => $amount,
                'source' => $token,
                'currency' => 'usd'
            ],
            ['api_key' => $this->key]
        );
    }
}
