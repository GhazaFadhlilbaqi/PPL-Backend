<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DebugMail extends Mailable
{
    use Queueable, SerializesModels;

    private $recepient;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($recepient)
    {
        $this->recepient = $recepient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.debug-mail', ['recepient' => $this->recepient]);
    }
}
