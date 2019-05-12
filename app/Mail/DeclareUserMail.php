<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use App\FamilyMember;
use Config;

class DeclareUserMail extends Mailable  implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $family;
    public $family_type;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(FamilyMember $family, $type)
    {
        //
        $this->family = $family;
        $this->family_type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->view('emails.family.declare_user')
        ->from(config('variable.ADMIN_EMAIL'))
        ->subject(config('variable.SITE_NAME').': ' .'Forgot Pin');
    }
}
