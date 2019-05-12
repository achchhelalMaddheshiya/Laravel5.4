<?php

namespace App\Jobs;

use App\FamilyMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FamilyRequestDeleteJob implements ShouldQueue
{

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
    public function __construct(array $family)
    {
        //
        $this->family = $family;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $isDeleted = FamilyMember::where('invited_by', $this->family['invitedby_id'])
            ->where('family_id', $this->family['family_id'])
            ->where('status', 0)->delete();

        return true;

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
