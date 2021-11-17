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
        $this->withoutExceptionHandling();

        $concert = Concert::factory()->create();
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);
        $ticket = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id
        ]);

        // visit the order confirmation page
        $response = $this->get("orders/ORDERCONFIRMATION1234");
        $response->assertStatus(200);

        //assert we see th correct order details
        $response->assertViewHas('order', function ($viewOrder) use ($order){
            return $viewOrder->id === $order->id;
        });
    }
}
