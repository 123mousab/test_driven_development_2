<?php

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Reservation;
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
        $concert = Concert::factory()->create(['ticket_price' => 1000])->addTickets(6);

        $tickets = $concert->findTickets(6);

        $reservation = new Reservation($tickets);

        $this->assertEquals(6000, $reservation->totalCost());
    }
}
