<?php


namespace App\Billing;


use Illuminate\Support\Str;

class FakePaymentGateway implements PaymentGateway
{
    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function newChargesDuring($callback)
    {
        $chargesFrom = $this->charges->count();
        $callback($this);
        return $this->charges->slice($chargesFrom)->reverse()->values();
    }

    public function getValidTestToken($cardNumber = '4242424242424242')
    {
        $token = 'fake_tok_'.Str::random(24);
        $this->tokens[$token] = $cardNumber;
        return $token;
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null)
        {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;
            $callback($this);
        }

        if (! $this->tokens->has($token))
        {
            throw new PaymentFailedException();
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4)
        ]);
    }

    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    public function beforeFirstCharge($callback)
    {
        return $this->beforeFirstChargeCallback = $callback;
    }
}
