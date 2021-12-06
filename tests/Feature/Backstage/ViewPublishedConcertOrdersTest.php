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

        $order = OrderFactory::createForConcert($concert, ['created_at' => Carbon::parse('11 days ago')],3);

        $response = $this->actingAs($user)->get("/backstage/published_concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.published.index');
        $this->assertTrue($response->data('concert')->is($concert));
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
