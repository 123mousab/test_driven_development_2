<?php

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_publish_their_own_concert()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $concert = Concert::factory()->unpublished()->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertRedirect('/backstage/concerts');
        $concert = $concert->fresh();
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    function a_concert_can_only_be_published_once()
    {
        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
            'ticket_quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id,
        ]);

        $response->assertStatus(422);
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }
}
