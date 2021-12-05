<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function can_get_formatted_date()
    {
        // Create a concert with known date
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 8:00pm')
        ]);

        // retrieve the formatted date
        $date = $concert->formatted_date;

        // verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }

    /**
     * @test
     */
    public function can_get_formatted_start_time()
    {
        // Create a concert with known date
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 19:00:00')
        ]);

        // retrieve the formatted date
        $time = $concert->formatted_time;

        // verify the date is formatted as expected
        $this->assertEquals('7:00pm', $time);
    }

    /**
     * @test
     */
    public function can_get_ticket_price_in_dollars()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 6750
        ]);

        $price = $concert->ticket_price_in_dollars;

        $this->assertEquals('67.50', $price);
    }

    /**
     * @test
     */
    public function concerts_with_a_published_at_date_are_published()
    {
        $publishedConcertA = Concert::factory()->create(['published_at' => Carbon::parse('- 1 week')]);
        $publishedConcertB = Concert::factory()->create(['published_at' => Carbon::parse('- 1 week')]);
        $publishedConcertC = Concert::factory()->create(['published_at' => null]);

        $concerts = Concert::published()->get();

        // verify the scope done
        $this->assertTrue($concerts->contains($publishedConcertA));
        $this->assertTrue($concerts->contains($publishedConcertB));
        $this->assertFalse($concerts->contains($publishedConcertC));
    }


    /**
     * @test
     */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        $this->assertEquals(2, $concert->ticketsRemaining());
    }

    /** @test */
    function tickets_sold_only_includes_tickets_associated_with_an_order()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        $this->assertEquals(3, $concert->ticketsSold());
    }

    /** @test */
    function total_tickets_includes_all_tickets()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        $this->assertEquals(5, $concert->totalTickets());
    }

    /** @test */
    function calculating_the_percentage_of_tickets_sold()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        // $this->assertEquals(0.285714286, $concert->percentSoldOut(), '', 0.00001);
        $this->assertEquals(60.00, $concert->percentSoldOut());
    }

    /**
     * @test
     */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception()
    {
        $this->withoutExceptionHandling();
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 10]);

        try {
            $reservation = $concert->reserveTickets(11, 'john@example.com');
        } catch (NotEnoughTicketsException $exception) {
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }

    /**
     * @test
     */
    public function can_reserve_available_tickets()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('john@example.com', $reservation->email());
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /**
     * @test
     */
    public function cannot_reserve_tickets_that_have_already_been_purchased()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $order = Order::factory()->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }
        $this->fail("Reserving tickets succeeded even though the tickets were already sold.");
    }

    function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }
        $this->fail("Reserving tickets succeeded even though the tickets were already reserved.");
    }

    /** @test */
    function concerts_can_be_published()
    {
        $concert = Concert::factory()->create(['published_at' => null]);
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(5, $concert->ticketsRemaining());
    }
}
