<?php

namespace Database\Factories;

use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'concert_id' => function(){
                return Concert::factory()->create()->id;
            },
        ];
    }

    public function reserved()
    {
        return $this->state(function (array $attributes) {
            return [
                'reserved_at' => Carbon::now(),
            ];
        });
    }
}
