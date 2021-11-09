<?php

namespace Tests\Unit;

use App\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Carbon\Carbon;
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
    function can_order_concert_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(10);
        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(5, $order->ticketQuantity());
    }

    /**
     * @test
     */
    function can_add_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /**
     * @test
     */
    function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = Concert::factory()->create()->addTickets(50);
        $concert->orderTickets('jane@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /**
     * @test
     */
    public function trying_to_purchase_more_tickets_than_remain_throws_an_exception()
    {
        $this->withoutExceptionHandling();
        $concert = Concert::factory()->create()->addTickets(10);

        try {
            $concert->orderTickets('jane@example.com', 11);
        } catch (NotEnoughTicketsException $exception) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Order succeeded even though there were not enough tickets remaining.");
    }

    /**
     * @test
     */
    public function cannot_order_tickets_that_have_already_been_purchased()
    {
        $concert = Concert::factory()->create()->addTickets(15);
        $concert->orderTickets('jane@example.com', 11);
        try {
            $concert->orderTickets('mousab@example.com', 6);
        } catch (NotEnoughTicketsException $exception) {
            $this->assertFalse($concert->hasOrderFor('mousab@example.com'));
            $this->assertEquals(4, $concert->ticketsRemaining());
            return;
        }

    }

    /**
     * @test
     */
    public function can_reserve_available_tickets()
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $this->assertEquals(3, $concert->ticketsRemaining());
        $reserveTickets = $concert->reserveTickets(2);

        $this->assertCount(2, $reserveTickets);
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /**
     * @test
     */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {
        $concert = Concert::factory()->create()->addTickets(3);
        $concert->reserveTickets(2);

        try {
            $concert->reserveTickets(2);
        }catch (NotEnoughTicketsException $exception)
        {
            $this->assertEquals(1, $concert->ticketsRemaining());
        }

//        $this->fail('Reserving tickets succeeded even though the tickets were already reserved.');
    }
}
