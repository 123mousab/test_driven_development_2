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
//            throw new PaymentFailedException();
            return false;
        }
    }

    public function getValidTestToken()
    {
        return \Stripe\Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => $this->key])->id;
    }

    public function newChargesDuring($callback)
    {
        $latestCharge = $this->lastCharge();
        $callback($this);
        return $this->newChargesSince($latestCharge)->pluck('amount');
    }

    private function lastCharge()
    {
        return \Stripe\Charge::all([
            'limit' => 1
        ], ['api_key' => $this->key])['data'][0];
    }

    private function newChargesSince($charge = null)
    {
        $newCharges = \Stripe\Charge::all([
            'ending_before' => $charge ? $charge->id : null,
        ], ['api_key' => $this->key])['data'];

        return collect($newCharges);
    }
}
