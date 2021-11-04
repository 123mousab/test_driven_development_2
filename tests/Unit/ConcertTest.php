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
}
