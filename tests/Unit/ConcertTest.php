<?php

namespace Tests\Unit;

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
    public function can_order_concert_tickets()
    {
        $concert = Concert::factory()->create();

        $order = $concert->orderTickets('jane@example.com', 3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }
}
