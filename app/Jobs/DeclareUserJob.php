<?php
namespace App\Jobs;

use App\FamilyMember;
use App\Mail\DeclareUserMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Mail;

class DeclareUserJob implements ShouldQueue
{

    use Dispatchable,
    InteractsWithQueue,
    Queueable,
        SerializesModels;

    public $family;
    public $family_type;

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
    public function __construct(FamilyMember $family, $type)
    {
        //
        $this->family = $family;
        $this->family_type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        dd($this->family);

        /*if ($this->family_type === 'primary') {
            // Send email to the creator of the folder
            //get guarantee user detail if found then send email for that user
            $guarantee = FamilyMember::where(["invited_by" => $this->family->invited_by])
                ->whereHas('familyTypeDetail', function ($q) {
                    $q->where('slug', "guarantee");
                })
                ->with(['familyTypeDetail' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }, 'receiverDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }])->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();
           
            Mail::to($this->family->senderDetail->email)->send(new DeclareUserMail($this->family, $this->family_type));

            if (!empty($guarantee) && !empty($guarantee->receiverDetail->email)) {
                Mail::to($guarantee->receiverDetail->email)->send(new DeclareUserMail($this->family, $this->family_type));
            }

        } else {
            dd('else');
            //get guarantee user detail if found then send email for that user
            $guarantee = FamilyMember::where(["invited_by" => $this->family->invited_by])
                ->whereHas('familyTypeDetail', function ($q) {
                    $q->where('slug', "primary");
                })
                ->with(['familyTypeDetail' => function ($q) {
                    $q->select('id', 'name', 'slug');
                }, 'receiverDetail' => function ($q) {
                    $q->select('id', 'slug', 'name', 'email', 'status');
                }])->select('id', 'invited_by', 'user_id', 'family_id', 'code', 'relation')->first();
            Mail::to($this->family->senderDetail->email)->send(new DeclareUserMail($this->family, $this->family_type));

            if (!empty($guarantee) && !empty($guarantee->receiverDetail->email)) {
                Mail::to($guarantee->receiverDetail->email)->send(new DeclareUserMail($this->family, $this->family_type));
            }

        }*/

    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(App\Jobs\Exception $exception)
    {
        // Send user notification of failure, etc...
         Log::info($exception);
    }

}
