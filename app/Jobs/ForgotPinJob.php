<?php
namespace App\Jobs;

use App\Mail\ForgotPinMail;
use App\FamilyMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Mail\Mailer;
use Mail;
use Illuminate\Support\Facades\Log;

class ForgotPinJob implements ShouldQueue {

    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $family;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FamilyMember $family) {
        //
        $this->family = $family;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        Mail::to($this->family->email)->send(new ForgotPinMail($this->family));
    }
    
    
    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
        Log::info($exception);
    }

}
