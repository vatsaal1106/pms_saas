<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceReminderAfterEvent
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notifyUser;
    public $invoice;
    public $reminder_after_days;

    public function __construct($invoice, $notifyUser, $reminder_after_days)
    {
        $this->invoice = $invoice;
        $this->notifyUser = $notifyUser;
        $this->reminder_after_days = $reminder_after_days;
    }

}
