<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
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
    public function customer_can_purchase_concert_tickets()
    {
        $this->withoutExceptionHandling();
        // Arrange
        // Create a concert
        $concert = Concert::factory()->create([
            'ticket_price' => 3250
        ]);

        // Act
        // Purchase concert tickets
        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken()
        ]);


        $response->assertStatus(201);

        // Assert
        // Make sure the customer was charged the correct amount
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure an order exists for this customer
        $order = $concert->orders->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     */
    public function email_is_required_to_purchase_tickets()
    {
        $concert = Concert::factory()->create();

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
        $concert = Concert::factory()->create();

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
        $concert = Concert::factory()->create();

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
        $concert = Concert::factory()->create();

          $response = $this->orderTickets($concert, [
              'email' => 'john@example.com',
              'ticket_quantity' 	=> 3,
          ]);

          $this->assertValidationError($response, 'payment_token');
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
