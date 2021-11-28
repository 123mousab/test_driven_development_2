<?php

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @test
     */
    public function a_ticket_can_be_reserved()
    {
        $ticket = Ticket::factory()->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }
    /**
     * @test
     */
    public function a_ticket_can_be_released()
    {
        $ticket = Ticket::factory()->reserved()->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->refresh()->reserved_at);
    }
    /**
     * @test
     */
    public function a_ticket_can_be_claimed_for_an_order()
    {
        $order = Order::factory()->create();
        $ticket = Ticket::factory()->create(['code' => null]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');
//        $this->assertNull($ticket->code);

        $ticket->claimFor($order);

        // assert the ticket is saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        // assert  the ticket had the expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
