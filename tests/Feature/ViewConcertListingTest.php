<?php

namespace Tests\Feature;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ViewConcertListingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function user_can_view_a_published_concert_listing()
    {
        // Arrange
        // Create a concert
        $concert = Concert::factory()->published()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('December 13, 2016 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
        ]);

        // Act
        // View the concert listing
        $response = $this->get('/concerts/'.$concert->id);

        // Assert
        // See the concert details
        $response->assertSeeText('The Red Chord');
        $response->assertSeeText('with Animosity and Lethargy');
        $response->assertSeeText('December 13, 2016');
        $response->assertSeeText('8:00pm');
        $response->assertSeeText('32.50');
        $response->assertSeeText('The Mosh Pit');
        $response->assertSeeText('123 Example Lane');
        $response->assertSeeText('Laraville, ON 17916');
        $response->assertSeeText('For tickets, call (555) 555-5555.');
    }

    /**
     * @test
     */
    public function user_cannot_view_unpublished_concert_listings()
    {
        $concert = Concert::factory()->unpublished()->create();

        $response = $this->get('/concerts/'.$concert->id);

        $response->assertStatus(404);
    }
}
