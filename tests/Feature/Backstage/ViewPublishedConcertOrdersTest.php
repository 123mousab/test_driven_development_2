<?php

namespace Tests\Feature\Backstage;

use App\Models\User;
use Carbon\Carbon;
use Database\Factories\ConcertFactory;
use Database\Factories\OrderFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published_concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published.index');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_can_view_the_most_10_recent_orders_for_their_concert()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $user->id]);
        $oldOrder = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);
        $recentOrder1 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('10 days ago')]);
        $recentOrder2 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('9 days ago')]);
        $recentOrder3 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('8 days ago')]);
        $recentOrder4 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('7 days ago')]);
        $recentOrder5 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('6 days ago')]);
        $recentOrder6 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('5 days ago')]);
        $recentOrder7 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('4 days ago')]);
        $recentOrder8 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('3 days ago')]);
        $recentOrder9 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('2 days ago')]);
        $recentOrder10 = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('1 days ago')]);

        $response = $this->actingAs($user)->get("/backstage/published_concerts/{$concert->id}/orders");

        $this->assertTrue($response->data('concert')->is($concert));

        $response->data('orders')->assertEquals([
            $recentOrder10,
            $recentOrder9,
            $recentOrder8,
            $recentOrder7,
            $recentOrder6,
            $recentOrder5,
            $recentOrder4,
            $recentOrder3,
            $recentOrder2,
            $recentOrder1,
        ]);
//        $response->data('orders')->assertContains($recentOrder10);
        $response->data('orders')->assertNotContains($oldOrder);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_unpublished_concerts()
    {
        $user = User::factory()->create();
        $concert = ConcertFactory::createUnpublished(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/backstage/published_concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_promoter_cannot_view_the_orders_of_another_published_concert()
    {
        $user = User::factory()->create();
        $otherUser =User::factory()->create();
        $concert = ConcertFactory::createPublished(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/published_concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_orders_of_any_published_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/published_concerts/{$concert->id}/orders");

        $response->assertRedirect('/login');
    }
}
