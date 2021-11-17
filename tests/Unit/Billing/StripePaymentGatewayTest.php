<?php

namespace Tests\Unit\Billing;

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Billing\PaymentGatewayContractTests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use RefreshDatabase, PaymentGatewayContractTests;

    private $stripe_api_key = 'sk_test_51IFf0EA6UNYC18RsKfYotpmcK9yhm95pUiLHTL8Ushu5m2D4n8THRp5AaYam5wPmYeidStqF5LKuMmqkbPh76NZn00tUx0CV64';

 /*   protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->lastCharge = $this->lastCharge();
    }*/

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway($this->stripe_api_key);
    }

//    /** @test */
//    function charges_with_an_invalid_payment_token_fail()
//    {
//
//        try {
//            $paymentGateway = new StripePaymentGateway($this->stripe_api_key);
//            $paymentGateway->charge(2500, 'invalid-payment_token');
//        }catch (PaymentFailedException $exception){
//            $this->assertCount(0, $this->newCharges());
//            $this->assertTrue(true);
//        }
//
//     /*   $paymentGateway = $this->getPaymentGateway();
//        $result = $paymentGateway->charge(2500, 'invalid-payment-token');
//        $this->assertFalse($result);*/
//    }
//
//    private function lastCharge()
//    {
//        return \Stripe\Charge::all(
//            ['limit' => 1],
//            ['api_key' => $this->stripe_api_key]
//        )['data'][0];
//    }
//
//
//    private function newCharges()
//    {
//        return \Stripe\Charge::all(
//            [
//                'ending_before' => $this->lastCharge ? $this->lastCharge->id : null,
//            ],
//            ['api_key' => $this->stripe_api_key]
//        )['data'];
//    }
//
//    private function validToken()
//    {
//        return \Stripe\Token::create([
//            "card" => [
//                "number" => "4242424242424242",
//                "exp_month" => 1,
//                "exp_year" => date('Y') + 1,
//                "cvc" => "123"
//            ]
//        ], ['api_key' => $this->stripe_api_key])->id;
//    }
}
