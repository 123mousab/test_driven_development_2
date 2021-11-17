<?php

namespace Tests\Feature;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;
    /**
     * @test
     */
    public function user_can_view_their_order_confiramtion()
    {
        $concert = Concert::factory()->create();
        $order = Order::factory()->create();
        $ticket = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

        // visit the order confirmation page
        $response = $this->get("orders/{$order->id}");

        //assert we see th correct order details
    }
}
