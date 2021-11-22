<?php

namespace App\Billing;

use App\OrderConfirmationNumberGenerator;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate()
    {
            return str_repeat('A',24);
    }
}
