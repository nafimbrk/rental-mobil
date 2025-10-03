<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PembayaranSewaSelesai
{
    use Dispatchable, SerializesModels;

    public $rental;
    public $customer;
    public $payment;

    public function __construct($rental, $customer, $payment)
    {
        $this->rental = $rental;
        $this->customer = $customer;
        $this->payment = $payment;
    }
}