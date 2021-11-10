<?php

namespace Tests\Unit;

use App\Billing\FakePaymentGateway;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @test
     */
    public function calculating_the_total_cost()
    {
        /*$concert = Concert::factory()->create(['ticket_price' => 1000])->addTickets(6);

        $tickets = $concert->findTickets(6);*/

        $tickets = collect([
            (object) ['price' => 2000],
            (object) ['price' => 2000],
            (object) ['price' => 2000]
        ]);

        $reservation = new Reservation($tickets, 'mousab@gmail.com');

        $this->assertEquals(6000, $reservation->totalCost());
    }

    /**
     * @test
     */
    public function retrieving_the_reservations_tickets()
    {
        $tickets = collect([
            (object) ['price' => 2000],
            (object) ['price' => 2000],
            (object) ['price' => 2000]
        ]);

        $reservation = new Reservation($tickets, 'mousab@gmail.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /**
     * @test
     */
    public function retrieving_the_customers_email()
    {
        $reservation = new Reservation(collect(), 'mousab@gmail.com');

        $this->assertEquals('mousab@gmail.com', $reservation->email());
    }

    /**
     * @test
     */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled()
    {
        $tickets = collect([
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
            \Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets,'mousab@gmail.com');

        $reservation->cancel();

        foreach ($tickets as $ticket)
        {
            $ticket->shouldHaveReceived('release');
        }
    }

    /**
     * @test
     */
    public function completing_a_reservation()
    {
        $concert = Concert::factory()->create(['ticket_price' => 1200]);
        $tickets = Ticket::factory()->count(3)->create(['concert_id' => $concert->id]);

        $reservation = new Reservation($tickets, 'mousab@salah.com');

        $paymentGateway = new FakePaymentGateway();
        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken());

        $this->assertEquals('mousab@salah.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals(3600, $paymentGateway->totalCharges());
    }
}
