<?php

namespace App\Billing;

use App\OrderConfirmationNumberGenerator;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate()
    {
//            return str_repeat('A',24);
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }
}
