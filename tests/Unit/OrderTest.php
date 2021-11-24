<?php

namespace Tests\Unit;

use App\Billing\Charge;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @test
     */
    public function creating_an_order_from_tickets_email_and_charge()
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

//        $tickets = Ticket::factory()->count(3)->create(['code' => '1234567']);
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);

        foreach ($order->tickets as $ticket)
        {
            $this->assertNotNull($ticket->code);
        }
    }

    /**
     * @test
     */
    public function converting_to_an_array()
    {
        /*$concert = Concert::factory()->create(['ticket_price' => 1200])->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);*/

        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000
        ]);

//        $order->tickets()->saveMany(Ticket::factory()->count(5)->create());

        $order->tickets()->saveMany([
            Ticket::factory()->create(['code' => 'TICKETCODE1']),
            Ticket::factory()->create(['code' => 'TICKETCODE2']),
            Ticket::factory()->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ], $result);
    }

    /**
     * @test
     */
    public function retrieving_an_order_by_confirmation_number()
    {
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

//    /**
//     * @test
//     */
//    public function retrieving_a_nonexistent_order_by_confirmation_number_throws_an_exception()
//    {
//        try {
//            Order::findByConfirmationNumber('NONEXISTENTCONFIRMATIONNUMBER');
//        }catch (ModelNotFoundException $exception)
//        {
//            return;
//        }
//
//        $this->fail('No matching order was found for the specified confirmation number, but an exception was not thrown.');
//    }
}
