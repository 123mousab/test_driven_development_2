<?php

namespace Tests\Feature\Backstage;

use App\Models\AttendeeMessage;
use App\Models\User;
use Database\Factories\ConcertFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MessageAttendeesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    function a_promoter_can_view_the_message_form_for_their_own_concert()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert_messages.new');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    function a_promoter_cannot_view_the_message_form_for_another_concert()
    {
        $user = User::factory()->create();

        $concert = ConcertFactory::createPublished([
            'user_id' => User::factory()->create(),
        ]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    function a_guest_cannot_view_the_message_form_for_any_concert()
    {
        $concert = ConcertFactory::createPublished();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /** @test */
    function a_promoter_can_send_a_new_message()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $concert = ConcertFactory::createPublished([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message',
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);
    }
}
