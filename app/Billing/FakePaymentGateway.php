<?php


namespace App\Billing;


class FakePaymentGateway implements PaymentGateway
{
    private $charges;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken()
    {
        return 'valid-token';
    }

    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null)
        {
            $this->beforeFirstChargeCallback->__invoke($this); // calling the variable
        }

        if ($token != $this->getValidTestToken())
        {
            throw new PaymentFailedException();
        }
        $this->charges[] = $amount;
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function beforeFirstCharge($callback)
    {
        return $this->beforeFirstChargeCallback = $callback;
    }
}
