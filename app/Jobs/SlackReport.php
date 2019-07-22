<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Redis;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SlackReport implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $title;
    private $title2;
    private $message;

    /**
     * Create a new job instance.
     * SlackReport constructor.
     * @param $title
     * @param $title2
     * @param $message
     */
    public function __construct($title, $title2, $message)
    {
        $this->title = $title;
        $this->title2 = $title2;
        $this->message = $message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Notification::send(
            new \App\Notifications\SlackRoute(),
            new \App\Notifications\SlackPublisher([
                'title' => $this->title,
                'title2' => $this->title2,
                'body' => $this->message
            ])
        );
    }
}
