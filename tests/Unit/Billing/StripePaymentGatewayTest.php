<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use PHPUnit\Framework\TestCase;

class StripePaymentGatewayTest extends TestCase
{
    private $stripe_api_key = 'sk_test_51IFf0EA6UNYC18RsKfYotpmcK9yhm95pUiLHTL8Ushu5m2D4n8THRp5AaYam5wPmYeidStqF5LKuMmqkbPh76NZn00tUx0CV64';

    /**
     * @test
     */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        // Create a new StripePaymentGateway
        $paymentGateway = new StripePaymentGateway($this->stripe_api_key);

        $token = \Stripe\Token::create([
            "card" => [
                "number" => "4242424242424242",
                "exp_month" => 1,
                "exp_year" => date('Y') + 1,
                "cvc" => "123"
            ]
        ], ['api_key' => $this->stripe_api_key])->id;

        // Create a new charge for some amount using a valid token
        $paymentGateway->charge(2500, $token);

        // Verify that the charge was completed successfully
        $lastCharge = \Stripe\Charge::all(
            ['limit' => 1],
            ['api_key' => $this->stripe_api_key]
        )['data'][0];

        $this->assertEquals(2500, $lastCharge->amount);
    }
}
