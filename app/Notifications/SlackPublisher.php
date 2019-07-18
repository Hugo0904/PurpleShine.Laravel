<?php
/**
 * Created by PhpStorm.
 * User: yueh
 * Date: 2018/6/9
 * Time: 下午3:17
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\SlackMessage;

class SlackPublisher extends Notification implements ShouldQueue
{
    use Queueable;

    private $message;
    private $config;

    /**
     * SlackPublish constructor.
     * @param array $message
     */
    public function __construct($message)
    {
        $this->message = $message;
        $this->config = config('slack');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)
            ->from(env('SLACK_USERNAME'))
            ->to(env('SLACK_EXCEPTION_CHANNEL'))
            ->error()
            ->content($this->message['title'])
            ->attachment(function ($attachment) {
                $attachment->title($this->message['title2'])
                    ->content($this->message['body']);
            });
    }
}