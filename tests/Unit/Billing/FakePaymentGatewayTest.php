<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

class FakePaymentGatewayTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function charge_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = new FakePaymentGateway();

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }

    /**
     * @test
     */
    public function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new FakePaymentGateway();
            $paymentGateway->charge(2500, 'invalid-payment_token');
        }catch (PaymentFailedException $exception){
            $this->assertTrue(true);
        }

    }

    /**
     * @test
     */
    public function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = new FakePaymentGateway();
        $callbackRan = false;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$callbackRan){ // reference
            $callbackRan = true;
            $this->assertEquals(0, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertTrue($callbackRan);
        $this->assertEquals(2500, $paymentGateway->totalCharges());
    }
}
