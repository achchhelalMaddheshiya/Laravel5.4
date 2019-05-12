<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use App\FamilyMember;
use Config;

class ForgotPinMail extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $family;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(FamilyMember $family)
    {
        //
        $this->family = $family;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->view('emails.family.forgot_pin')
        ->from(config('variable.ADMIN_EMAIL'))
        ->subject(config('variable.SITE_NAME').': ' .'Forgot Pin');
    }
}
