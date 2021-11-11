<?php


namespace App\Billing;


use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function charge($amount, $token)
    {
        try {
            \Stripe\Charge::create(
                [
                    'amount' => $amount,
                    'source' => $token,
                    'currency' => 'usd'
                ],
                ['api_key' => $this->key]
            );
        }catch (InvalidRequestException $exception)
        {
            throw new PaymentFailedException();
        }
    }
}
