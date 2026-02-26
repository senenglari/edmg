<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OutgoingMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subject($this->details['title'])
             ->from(env("MAIL_USERNAME"), 'Automatic Mail System')
                    ->view('email.outgoing', $this->details);
  
        foreach ($this->details['files'] as $file){
            $this->attach($file);
        }
  
        return $this;
    }
}
