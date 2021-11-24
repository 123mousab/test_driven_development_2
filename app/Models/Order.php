<?php

namespace App\Models;

use App\Facades\OrderConfirmationNumber;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function findByConfirmationNumber($confirmationNumber)
    {
        return self::where('confirmation_number', $confirmationNumber)->firstOrFail();
    }

    public static function forTickets($tickets, $email, $charge)
    {
        $order = self::create([
//            'confirmation_number' => app(OrderConfirmationNumberGenerator::class)->generate(),
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour(),
        ]);
        // update the order of ticket
       /* foreach ($tickets as $ticket) {
            $order->tickets()->save($ticket);
        }*/
        $order->tickets()->saveMany($tickets);
        return $order;
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketQuantity()
    {
        return $this->tickets()->count();
    }

    public function toArray()
    {
        return [
            'confirmation_number' =>  $this->confirmation_number,
            'email' => $this->email,
            'ticket_quantity' => $this->ticketQuantity(),
            'amount' => $this->amount,
        ];
    }
}
