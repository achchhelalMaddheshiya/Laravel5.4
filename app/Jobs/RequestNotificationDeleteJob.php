<?php

namespace App\Jobs;

use App\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RequestNotificationDeleteJob implements ShouldQueue
{

    use Dispatchable,
    InteractsWithQueue,
    Queueable,
        SerializesModels;

    public $notification;

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
    public function __construct(array $notification)
    {
        //
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $isDeleted = Notification::where('sender_id', $this->notification['invitedby_id'])
            ->where('family_id', $this->notification['family_id'])
            ->where('receiver_id',NULL)->delete();

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
