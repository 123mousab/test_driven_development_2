<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    private $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp(); //
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /**
     * @test
     */
    public function customer_can_purchase_tickets_to_a_published_concert()
    {
        $this->withoutExceptionHandling();

        $orderConfirmationNumberGenerator = \Mockery::mock(OrderConfirmationNumberGenerator::class, [
            'generate' => 'ORDERCONFIRMATION1234',
        ]);
        $this->app->instance(OrderConfirmationNumberGenerator::class, $orderConfirmationNumberGenerator);

        $concert = Concert::factory()->published()->create(['ticket_price' => 3250])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);
        $response->assertStatus(201);

        $response->assertJson([
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'amount' => 9750,
        ]);
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /**
     * @test
     */
    function cannot_purchase_more_tickets_than_remain()
    {
        $concert = Concert::factory()->published()->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /**
     * @test
     */
    function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase()
    {

        $concert = Concert::factory()->published()->create([
            'ticket_price' => 1200
        ])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {

            $requestA = $this->app['request'];

            $response = $this->orderTickets($concert, [
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $this->app['request'] = $requestA;

            $response->assertStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $response = $this->orderTickets($concert, [
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    /**
     * @test
     */
    function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = Concert::factory()->unpublished()->create([
            'ticket_price' => 3250
        ])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }
    /**
     * @test
     */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'ticket_quantity' 	=> 3,
            'payment_token' 	=> $this->paymentGateway->getValidTestToken()
        ]);

        $this->assertValidationError($response, 'email');

       /* $response = $this->pos('POST', "/concerts/{$concert->id}/orders", [
            // We do not provide email address
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->decodeResponseJson()['errors']);*/
    }

    /**
     * @test
     */
    public function ticket_quantity_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function ticket_quantity_is_least_1_purchase_tickets()
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' 	=> 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError($response, 'ticket_quantity');
    }

    /**
     * @test
     */
    public function payment_token_is_required()
    {
        $concert = Concert::factory()->published()->create();

          $response = $this->orderTickets($concert, [
              'email' => 'john@example.com',
              'ticket_quantity' 	=> 3,
          ]);

          $this->assertValidationError($response, 'payment_token');
    }

    /**
     * @test
     */
    function an_order_is_not_created_if_payment_fails()
    {
        $concert = Concert::factory()->published()
            ->create(['ticket_price' => 3250])
            ->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    private function orderTickets($concert, $params)
    {
        return $this->post("/concerts/$concert->id/orders", $params);
    }

    protected function assertValidationError($response,$field)
    {
        return $response->assertSessionHasErrors($field);
    }
}
