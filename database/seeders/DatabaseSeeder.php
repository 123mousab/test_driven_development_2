<?php

namespace Database\Seeders;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        Concert::factory()->published()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'venue' => 'The Most Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 3250,
            'additional_information' => 'This concert is 19+.',
        ])->addTickets(10);
    }
}
