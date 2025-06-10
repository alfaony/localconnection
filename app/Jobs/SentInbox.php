<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\InboxHelper;

class SentInbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $from;
    public $to;
    public $message;
    public $directUrl;

    public function __construct($from,$to,$message,$directUrl)
    {
        $this->from = $from;
        $this->to = $to;
        $this->message = $message;
        $this->directUrl = $directUrl;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $this->to,
            $this->from,
            $this->message,
            $this->directUrl
        );

        return true;
    }
}
