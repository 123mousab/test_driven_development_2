<?php


namespace App\Billing;


use Stripe\Exception\InvalidRequestException;

class StripePaymentGateway implements PaymentGateway
{
    const TEST_CARD_NUMBER = '5105105105105100';
    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function charge($amount, $token)
    {
        try {
            $stripeCharge = \Stripe\Charge::create(
                [
                    'amount' => $amount,
                    'source' => $token,
                    'currency' => 'usd'
                ],
                ['api_key' => $this->key]
            );

            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['payment_method_details']['card']['last4']
            ]);
        }catch (InvalidRequestException $exception)
        {
//            throw new PaymentFailedException();
            return false;
        }
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        return \Stripe\Token::create([
            "card" => [
                "number" => $cardNumber,
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
        return $this->newChargesSince($latestCharge)->map(function ($stripeCharge){
            return new Charge([
                'amount' => $stripeCharge['amount'],
                'card_last_four' => $stripeCharge['payment_method_details']['card']['last4']
            ]);
        });
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
