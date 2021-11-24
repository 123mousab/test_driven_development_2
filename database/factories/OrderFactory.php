<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => 5250,
            'email' => 'somebody@example.com',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'card_last_four' => '1234',
        ];
    }
}
